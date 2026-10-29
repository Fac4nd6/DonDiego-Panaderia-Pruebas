CREATE TABLE IF NOT EXISTS `rate_limit_buckets` (
  `bucket` varchar(40) NOT NULL,
  `subject_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `window_started_at` datetime NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`bucket`, `subject_hash`),
  KEY `idx_rate_limit_expiry` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;