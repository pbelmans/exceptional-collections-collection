import feedparser
import Levenshtein
import pprint # TODO remove later on
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
    return True # TODO implement check
  elif site == "zbMath":
    return True # TODO implement check
  else:
    return True # TODO error

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
  def produceNames(first, last):
    return ["{0} {1}".format(first, last), "{0} {1}".format(last, first), last]

  names = []

  try:
    query = "SELECT id, firstname, lastname FROM authors"
    cursor.execute(query)

    names = cursor.fetchall()

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return [(author, first, last) for (article, first, last) in names if any([Levenshtein.ratio(new, name) > 0.9 for name in produceNames(first, last)])]

# creates an article in the database
def createArticle(title):
  try:
    query = "INSERT INTO articles (title) VALUES (?)"
    cursor.execute(query, (title,))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return cursor.lastrowid

# set the identifier and category for an article
def setArXivIdentifier(article, identifier, category):
  assert isValidIdentifier("arXiv", identifier)
  assert articleExists(article)

  try:
    query = "INSERT INTO arxiv (article, identifier, category) VALUES (?, ?, ?)"
    cursor.execute(query, (article, identifier, category))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]


"""
the actual magic
"""

# try adding an article
# returns (added, article)
def addArticle(title):
  similar = getSimilarTitles(title)

  # if there are collisions we ask the user whether the article already exists
  if len(similar) > 0:
    print "Found similar items:"
    for (article, title) in similar:
      print " {0}) {1}".format(article, title)

    while True:
      answer = raw_input("Is the article '{0}' any of the above? (Y/N): ".format(title))

      # if yes then we ask for the collision
      if answer == "Y":
        if verbose: print "Not adding '{0}'\n".format(title)

        # for later use we return the id of the already existing article
        while True:
          answer = raw_input("Which of the articles is it? ")

          print [article for (article, title) in similar]

          if answer in [str(article) for (article, title) in similar]:
            return (False, answer)

        return 0

      # if no then we add the article
      if answer == "N": break

  # there are no collisions so we can add the article
  article = createArticle(title)

  if verbose: "Added article {0} with title {1}".format(article, title)

  return (True, article)

def addAuthor(author):
  return True

def arXivImporter(identifier):
  assert isValidIdentifier("arXiv", identifier)

  # API call
  URL = "http://export.arxiv.org/api/query?id_list={0}".format(identifier)
  
  feed = feedparser.parse(URL)

  entry = feed["entries"][0]

  # collect the data from the feed
  title = entry["title"]
  authors = [author["name"] for author in entry["authors"]]
  category = entry["tags"][0]["term"]

  # try adding the article
  (added, article) = addArticle(title)

  # if the article was added we try adding the authors and the arXiv identifier
  if added:
    # try adding the authors of the article
    for name in authors:
      author = addAuthor(name)
      # TODO link the article to the author

    # associate arXiv identifier and category to it
    setArXivIdentifier(article, identifier, category)
    if verbose: print "Associated arXiv identifier {0} and category {1} to article {2}".format(identifier, category, article)

  # if the article already existed we only try updating the arXiv identifier
  else:
    # TODO check whether the article already has an arXiv id: if yes error, if no set it
    print ""

  if verbose: print ""

  
arXivImporter("1504.01776")
arXivImporter("1411.1799")
arXivImporter("1410.5207")
arXivImporter("1503.03992")

general.close(connection)

"""
WORKFLOW
1) run an importer using arXiv, MSC or zbMath
2) compare title using string distance thingie to check for collisions
3) if no collision detected: create article

"""
