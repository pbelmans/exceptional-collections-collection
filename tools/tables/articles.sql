CREATE TABLE "articles" (
  "id" INTEGER PRIMARY KEY,
  -- information about the article
  "title" VARCHAR,
  "year"  INTEGER,
  -- online databases
  "mr"     VARCHAR UNIQUE,
  "zbmath" VARCHAR UNIQUE,
  "arxiv"  VARCHAR UNIQUE
);
