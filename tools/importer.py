import bibtexparser
import feedparser
import Levenshtein
import requests
import sqlite3

import general

verbose = True

(connection, cursor) = general.connect()

"""
checks
"""

# checks whether an identifier is valid
def isValidIdentifier(site, identifier):
  if   site == "arXiv":
    return True # TODO implement check
  elif site == "MSC":
    return identifier[0:2] == "MR" and identifier[2:].isdigit()
  elif site == "zbMath":
    return True # TODO implement check
  else:
    return False # TODO error

def articleExists(article):
  # TODO implement this
  return True


"""
database interaction
"""

# look for similar titles
def getSimilarTitles(new):
  articles = []

  try:
    query = "SELECT id, title FROM articles"
    cursor.execute(query)

    articles = cursor.fetchall()

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return [(article, title) for (article, title) in articles if Levenshtein.ratio(new, title) > 0.9]

# look for similar names
def getSimilarNames(new):
  # produce reasonable sounding alternatives for a name
  def produceNames(first, last):
    return [unicode("{0} {1}").format(first, last), unicode("{0} {1}").format(last, first), last]

  names = []

  try:
    query = "SELECT id, firstname, lastname FROM authors"
    cursor.execute(query)

    names = cursor.fetchall()

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  candidates = sum([produceNames(first, last) for (author, first, last) in names], [])

  return [(author, first, last) for (author, first, last) in names if any([Levenshtein.ratio(unicode(new), name) > 0.7 for name in produceNames(first, last)])]

# creates an article in the database
def createArticle(title):
  try:
    query = "INSERT INTO articles (title) VALUES (?)"
    cursor.execute(query, (title,))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return cursor.lastrowid

# creates an author in the database
def createAuthor(first, last):
  try:
    query = "INSERT INTO authors (firstname, lastname) VALUES (?, ?)"
    cursor.execute(query, (first, last))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return cursor.lastrowid

# link an article and an author
def addAuthorship(article, author):
  try:
    query = "INSERT INTO authorship (article, author) VALUES (?, ?)"
    cursor.execute(query, (article, author))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

# set the arXiv identifier and category for an article
def setArXivIdentifier(article, identifier, category):
  assert isValidIdentifier("arXiv", identifier)
  assert articleExists(article)

  try:
    query = "UPDATE articles SET arxiv = ?, arxivcategory = ? WHERE id = ?"
    cursor.execute(query, (identifier, category, article))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

# set the MSC identifier for an article
def setMSCIdentifier(article, identifier):
  assert isValidIdentifier("MSC", identifier)
  assert articleExists(article)

  try:
    query = "UPDATE articles SET msc = ? WHERE id = ?"
    cursor.execute(query, (identifier, article))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

# set the year for an article
def setYear(article, year):
  assert articleExists(article)

  try:
    query = "UPDATE articles SET year = ? WHERE id = ?"
    cursor.execute(query, (year, article))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]



"""
the actual magic
"""

# try adding an article, returns (added, article)
def addArticle(title):
  similar = getSimilarTitles(title)

  # if there are collisions we ask the user whether the article already exists
  if len(similar) > 0:
    # give them the options
    print "Found similar articles:"
    for (article, title) in similar:
      print " {0}) {1}".format(article, title)

    # try to resolve the collision
    while True:
      answer = raw_input("Is the article '{0}' any of the above? (Y/N): ".format(title))

      # if yes then we ask for the collision
      if answer == "Y":
        if verbose: print "Not adding '{0}'\n".format(title)

        # for later use we return the id of the already existing article
        while True:
          answer = raw_input("Which of the articles is it? ")

          if answer in [str(article) for (article, title) in similar]:
            return (False, answer)

        return 0

      # if no then we add the article
      if answer == "N": break

  # there are no collisions so we can add the article
  article = createArticle(title)

  if verbose: "Added article {0} with title {1}".format(article, title)

  return (True, article)

# add the authors and link the article
def addAuthors(article, names):
  for (first, last) in names:
    (added, author) = addAuthor(first, last)

    addAuthorship(article, author)

# try adding an author, returns (added, author)
def addAuthor(first, last):
  similar = getSimilarNames(unicode("{0} {1}").format(first, last))

  # if there are collisions we ask the user whether the author already exists
  if len(similar) > 0:
    # give them the options
    print "Found authors similar to {0} {1}:".format(first, last)
    for (author, first, last) in similar:
      print " {0}) {1} {2}".format(author, first, last)

    # try to resolve the collision
    while True:
      answer = raw_input(unicode("Is the author '{0} {1}' any of the above? (Y/N): ").format(first, last))

      # if yes then we ask for the collision
      if answer == "Y":
        if verbose: print unicode("Not adding '{0} {1}'\n").format(first, last)

        # for later use we return the id of the already existing author
        while True:
          answer = raw_input("Which of the authors is it? ")

          if answer in [str(author) for (author, first, last) in similar]:
            return (False, answer)

        return 0

      # if no then we add the author
      if answer == "N": break

  # there are no collisions so we can add the article
  author = createAuthor(first, last)

  if verbose: unicode("Added author {0} with name '{1} {2}'").format(author, first, last)

  return (True, author)

"""
generic importer functionality, so the workflow is:
  1) specific importer (e.g. arXivImporter) fetches data
  2) creates article and authors object
  3) tries adding the code, resolving collisions etc.
  4) add extra importer-specific information (e.g. arXiv identifiers)
"""

def importer(title, authors):
  # try adding the article
  (added, article) = addArticle(title)

  # if the article was added we try adding the authors to the database and linking the article to them
  if added: addAuthors(article, authors)

  return (added, article)


"""
importing from arXiv
"""

def arXivImporter(identifier):
  assert isValidIdentifier("arXiv", identifier)

  # API call
  URL = "http://export.arxiv.org/api/query?id_list={0}".format(identifier)
  
  feed = feedparser.parse(URL)

  entry = feed["entries"][0]

  # collect the data from the feed
  title = entry["title"]
  authors = [author["name"].split(" ", 1) for author in entry["authors"]]

  # add the article and authors if necessary
  (added, article) = importer(title, authors)

  # handle arXiv specific information
  category = entry["tags"][0]["term"]

  # if the article was added we update the arXiv identifier accordingly
  if added:
    # associate arXiv identifier and category to it
    setArXivIdentifier(article, identifier, category)
    if verbose: print "Associated arXiv identifier {0} and category {1} to article {2}".format(identifier, category, article)

  # if the article already existed we only try updating the arXiv identifier
  else:
    # TODO check whether the article already has an arXiv id: if yes error, if no set it
    setArXivIdentifier(article, identifier, category)
    print ""

  if verbose: print ""


"""
importing from Mathematical Reviews

This code is modified from https://github.com/pbelmans/mscget
"""

# check whether we are authenticated by making an empty request
def isAuthenticated():
  # make the request
  payload = {"fn": 130}
  r = requests.get(path, params=payload)

  if r.status_code == 200:
    return True
  elif r.status_code == 401:
    return False
  else:
    raise Exception("Received HTTP status code " + str(r.status_code))

class KeyNotFoundException(Exception):
  def __init__(self, key):
    self.key = key

  def __str__(self):
    return key + " was not found"

class AuthenticationException(Exception):
  def __str__(self):
    return "Not authenticated"

# path for the API
path = "http://www.ams.org/msnmain"

# obtain the BibTeX code for a Mathematical Review
def getBibTeXFromMSC(identifier):
  assert isValidIdentifier("MSC", identifier)

  # reconstructing the BibTeX code block
  inCodeBlock = False
  code = ""

  # make the request
  payload = {"fn": 130, "fmt": "bibtex", "pg1": "MR", "s1": identifier}
  r = requests.get(path, params=payload)

  # 401 means not authenticated
  if r.status_code == 401:
    raise AuthenticationException()

  # anything but 200 means something else went wrong
  if not r.status_code == 200:
    raise Exception("Received HTTP status code " + str(r.status_code))

  for line in r.text.split("\n"):
    if "No publications results for" in line:
      raise KeyNotFoundException(identifier)


    if line.strip() == "</pre>": inCodeBlock = False

    if inCodeBlock:
      code = code + "\n" + line

    if line.strip() == "<pre>": inCodeBlock = True

  return code

def MRImporter(identifier):
  bibtex = getBibTeXFromMSC(identifier)

  entry = bibtexparser.loads(bibtex).entries[0]

  title = entry["title"]
  authors = entry["author"]
  authors = authors.split(" and ")
  authors = [(name[1], name[0]) for name in [name.split(",") for name in authors]]
  year = entry["year"]

  # add the article and authors if necessary
  (added, article) = importer(title, authors)

  setYear(article, year)

  # if the article was added we update the MSC identifier accordingly
  if added:
    # associate MSC identifier and category to it
    setMSCIdentifier(article, identifier)
    if verbose: print "Associated MSC identifier {0} to article {1}".format(identifier, article)

  # if the article already existed we only try updating the MSC identifier
  else:
    # TODO check whether the article already has an MSC id: if yes error, if no set it
    setMSCIdentifier(article, identifier)
    print ""

  if verbose: print ""

  if verbose: print ""


general.close(connection)

"""
WORKFLOW
1) run an importer using arXiv, MSC or zbMath
2) compare title using string distance thingie to check for collisions
3) if no collision detected: create article

"""
