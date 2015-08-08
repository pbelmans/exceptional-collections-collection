import ConfigParser
import sqlite3

import pprint


# get the database location
config = ConfigParser.ConfigParser()
config.read("../config.ini")

# the path should be interpreted relative to the root directory
path = "../" + config.get("database", "path")

# connect to the database
def connect():
  connection = sqlite3.connect(path)

  return (connection, connection.cursor())

# close the database connection
def close(connection):
  connection.commit()
  connection.close()

"""
lookup methods
"""
def getArticle(cursor, site, identifier):
  try:
    if site == "arXiv":
      query = "SELECT id, title FROM articles WHERE arxiv = ?"
    elif site == "MSC":
      query = "SELECT id, title FROM articles WHERE msc = ?"
    elif site == "zbMath":
      query = "SELECT id, title FROM articles WHERE zbmath = ?"

    cursor.execute(query, (identifier,))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return cursor.fetchone()

def getKeyword(cursor, keyword):
  try:
    query = "SELECT id, keyword, slug, description FROM keywords WHERE keyword = ?"
    cursor.execute(query, (keyword,))

  except sqlite3.Error, e:
    print "An error occurred:", e.args[0]

  return cursor.fetchone()
