CREATE TABLE "articles" (
  "id" INTEGER PRIMARY KEY,
  "title" VARCHAR,
  "year" INTEGER,
  -- arXiv
  "arxiv" INTEGER UNIQUE,
  "arxivcategory" VARCHAR,
  -- msc
  "msc" VARCHAR UNIQUE
);
