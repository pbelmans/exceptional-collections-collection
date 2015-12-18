import general
import importer

def importFile(filename, site):
  f = open(filename, "r")

  for line in f:
    if len(line.strip()) == 0:
      continue

    if not importer.identifierExists(site, line.strip()):
      if site == "arXiv":
        importer.arXivImporter(line.strip())
      elif site == "MSC":
        importer.MRImporter(line.strip())

importFile("arXiv.txt", "arXiv")
importFile("MSC.txt", "MSC")

general.close(importer.connection)
