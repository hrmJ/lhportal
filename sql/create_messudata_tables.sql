create table `messut` (
  `id` int unsigned not null auto_increment,
  `pvm` date not null,
  `teema` varchar(100) not null,
  primary key(`id`)
);

create table `vastuut` (
  `id` int unsigned not null auto_increment,
  `messu_id` int unsigned not null,
  `vastuu` varchar(100) not null,
  `kommentit` TEXT  not null,
  index messu_index(`messu_id`),
  foreign key (`messu_id`) references messut(`id`) on delete cascade,
  primary key(`id`)
);
