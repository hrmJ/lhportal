create table `kaudet` (
  `id` int unsigned not null auto_increment,
  `alkupvm` date not null,
  `loppupvm` date not null,
  `tyyppi` varchar(100),
  `teema` varchar(100),
  `kommentit` TEXT,
   primary key(`id`)
);

create table `messut` (
  `id` int unsigned not null auto_increment,
  `pvm` date not null,
  `teema` varchar(100) not null,
   primary key(`id`)
);

create table `vastuut` (
  `id` int unsigned not null auto_increment,
  `messu_id` int unsigned not null,
  `vastuu` varchar(100),
  `vastuullinen` varchar(100),
  `kommentit` TEXT,
  index messu_index(`messu_id`),
  foreign key (`messu_id`) references messut(`id`) on delete cascade,
  primary key(`id`)
);


create table `comments`(
  `id` int unsigned not null auto_increment,
  `messu_id` int unsigned not null,
  `content` TEXT,
  `commentator` varchar(100),
  `comment_time` DATETIME not null,
  index messu_index(`messu_id`),
  foreign key (`messu_id`) references messut(`id`) on delete cascade,
  primary key(`id`)
);
