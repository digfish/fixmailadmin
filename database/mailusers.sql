
-- --------------------------------------------------------

--
-- Table structure for table `domains`
--

CREATE TABLE IF NOT EXISTS `domains` (
  `domain` varchar(50) NOT NULL
);

--
-- Dumping data for table `domains`
--

INSERT INTO `domains` (`domain`) VALUES
('digfish.org');

-- --------------------------------------------------------

--
-- Table structure for table `forwardings`
--

CREATE TABLE IF NOT EXISTS `forwardings` (
  `source` varchar(80) NOT NULL,
  `destination` text NOT NULL
);

--
-- Dumping data for table `forwardings`
--

INSERT INTO `forwardings` (`source`, `destination`) VALUES
('blog@digfish.org', 'sam@digfish.org'),
('abuse@digfish.org', 'postmaster@newvps.digfish.org\r\n'),
('postmaster@digfish.org', 'postmaster@newvps.digfish.org');

-- --------------------------------------------------------

--
-- Table structure for table `transport`
--

CREATE TABLE IF NOT EXISTS `transport` (
  `domain` varchar(128) NOT NULL DEFAULT '',
  `transport` varchar(128) NOT NULL DEFAULT ''
);

--
-- Dumping data for table `transport`
--

INSERT INTO `transport` (`domain`, `transport`) VALUES
('casa-viana.com', 'smtp:casa-viana.com');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `email` varchar(80) NOT NULL,
  `password` varchar(20) NOT NULL,
  `quota` int(10) DEFAULT '10485760'
);

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`email`, `password`, `quota`) VALUES
('sam@digfish.org', 'x/UJGM6Dzd.pk', 1073741824),
('mario@digfish.org', 'HMA/lzgbjcxe.', 10485760),
('teste@digfish.org', 'w/rAqLDg4VxsY', 10485760);

