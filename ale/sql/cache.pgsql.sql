CREATE TABLE alecache (
  host varchar(64) NOT NULL,
  path varchar(64) NOT NULL,
  params varchar(64) NOT NULL,
  content text NOT NULL,
  cachedUntil timestamp with time zone default NULL,
  PRIMARY KEY  (host,path,params)
);
