import sqlite3

import general

verbose = True

(connection, cursor) = general.connect()

# check whether a keyword already exists
def keywordExists(keyword):
  try:
    query = "SELECT COUNT(*) FROM keywords WHERE keyword = ?"
    cursor.execute(query, (keyword,))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return int(cursor.fetchone()[0]) > 0

# check whether a slug already exists
def slugExists(slug):
  try:
    query = "SELECT COUNT(*) FROM keywords WHERE slug = ?"
    cursor.execute(query, (slug,))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return int(cursor.fetchone()[0]) > 0

# check whether an association exists
def associationExists(article, keyword):
  try:
    query = "SELECT COUNT(*) FROM articlekeywords WHERE article = ? AND keyword = ?"
    cursor.execute(query, (article, keyword))

    return int(cursor.fetchone()[0]) > 0

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return False


# create a new keyword
def createKeyword(keyword, slug, description):
  assert not keywordExists(keyword)
  assert not slugExists(keyword)

  try:
    query = "INSERT INTO keywords (keyword, slug, description) VALUES (?, ?, ?)"
    cursor.execute(query, (keyword, slug, description))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

# create an association between a keyword and an article
def associateKeyword(article, keyword):
  assert not associationExists(article, keyword)

  try:
    query = "INSERT INTO articlekeywords (article, keyword) VALUES (?, ?)"
    cursor.execute(query, (article, keyword))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

#createKeyword("homological projective duality", "hpd", "awesomeness")

#article = general.getArticle(cursor, "MSC", "MR2354207")
keyword = general.getKeyword(cursor, "homological projective duality")
#associateKeyword(article[0], keyword[0])

article = general.getArticle(cursor, "MSC", "MR2238172")
associateKeyword(article[0], keyword[0])

general.close(connection)
