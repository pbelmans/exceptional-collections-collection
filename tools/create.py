import ConfigParser
import os
import sqlite3

# determine where the database must be created
config = ConfigParser.ConfigParser()
config.read("../config.ini")

# the path should be interpreted relative to the root directory
path = "../" + config.get("database", "path")
# override this if you want the file to be created elsewhere


def createTable(filename):
  query = open("tables/" + filename, "r").read()
  cursor = connection.cursor()
  cursor.executescript(query)

tables = ["articles.sql",
          "arxiv.sql",
          "authors.sql",
          "authorship.sql",
          "keywords.sql",
          "types.sql"
]

if os.path.isfile(path):
  print "The file {0} already exists, aborting".format(path)
else:
  print "Creating the database in {0}".format(path)
  
  connection = sqlite3.connect(path)
  
  map(createTable, tables)
  
  connection.commit()
  connection.close()
  
  print "The database has been created"
