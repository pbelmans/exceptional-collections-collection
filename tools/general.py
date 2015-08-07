import ConfigParser
import sqlite3

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
