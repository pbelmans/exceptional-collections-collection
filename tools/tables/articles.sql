CREATE TABLE "articles" (
  "id" INTEGER PRIMARY KEY,
  "title" VARCHAR,
  "year" INTEGER,
  -- arXiv
  "arxiv" VARCHAR UNIQUE,
  "arxivcategory" VARCHAR,
  -- msc
  "msc" VARCHAR UNIQUE
);
