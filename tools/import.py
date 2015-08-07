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

def arXivImporter(identifier):
  assert isValidIdentifier("arXiv", identifier)

  # API call
  URL = "http://export.arxiv.org/api/query?id_list={0}".format(identifier)
  
  feed = feedparser.parse(URL)

  #pprint.pprint(feed)

  #print feed["entries"][0]["authors"]
  #print feed["entries"][0]["published"]
  #print feed["entries"][0]["title"]
  #print feed["entries"][0]["tags"][0]["term"] # category

  entry = feed["entries"][0]

  similar = getSimilarTitles(entry["title"])

  # create the article
  article = createArticle(entry["title"])
  if verbose: print "Created article {0} with title {1}".format(article, entry["title"])

  # associate arXiv identifier and category to it
  category = entry["tags"][0]["term"]
  setArXivIdentifier(article, identifier, category)
  if verbose: print "Associated arXiv identifier {0} and category {1} to article {2}".format(identifier, category, article)

  
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
