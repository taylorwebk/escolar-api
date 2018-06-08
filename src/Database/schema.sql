-- ESTUDIANTE, APODERADO, DOCENTE, REGENTE
-- ADMINISTRADOR, MATERIA, CURSO, TRABAJO, BIMESTRE
/*
apoderado: tabla para almacenar a los apoderados de los
estudiantes inscritos
*/
drop database if exists escolardb;
CREATE DATABASE escolardb DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;
use escolardb;
create table apoderado(
  id integer not null auto_increment,
  nombre varchar(255),
  nroref varchar(127),
  parentesco varchar(127),
  primary key(id)
);
create table estudiante(
  id integer not null auto_increment,
  apoderado_id integer not null,
  ci varchar(63),
  nombres varchar(127),
  appat varchar(127),
  apmat varchar(127),
  username varchar(127),
  dir varchar(255),
  nrocel varchar(127),
  password varchar(257),
  primary key(id),
  foreign key(apoderado_id)
  references apoderado(id)
  on delete cascade
);
create table curso(
  id integer not null auto_increment,
  nro tinyint,
  paralelo enum('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'),
  estado boolean default 0,
  primary key(id)
);
create table gestion(
  id integer not null auto_increment,
  nro integer,
  primary key(id)
);
create table bimestre(
  id integer not null auto_increment,
  nro integer,
  primary key(id)
);
create table bimestre_gestion(
  gestion_id integer not null,
  bimestre_id integer not null,
  active boolean default 0,
  primary key(gestion_id, bimestre_id),
  foreign key(gestion_id)
  references gestion(id)
  on delete cascade,
  foreign key(bimestre_id)
  references bimestre(id)
  on delete cascade
);
create table materia(
  id integer not null auto_increment,
  nombre varchar(63),
  nombremin varchar(63),
  campo varchar(63),
  primary key(id)
);
create table hora(
  id integer not null auto_increment,
  ini time,
  fin time,
  primary key(id)
);
create table dia(
  id integer not null auto_increment,
  literal varchar(63),
  primary key(id)
);
create table periodo(
  id integer not null auto_increment,
  nro integer,
  hora_id integer not null,
  dia_id integer not null,
  primary key(id),
  foreign key(hora_id)
  references hora(id)
  on delete cascade,
  foreign key(dia_id)
  references dia(id)
  on delete cascade
);
create table profesor(
  id integer not null auto_increment,
  nombres varchar(127),
  apmat varchar(127),
  appat varchar(127),
  ci varchar(63),
  password varchar(257),
  dir varchar(127),
  primary key(id)
);
create table materia_profesor(
  materia_id integer not null,
  profesor_id integer not null,
  estado boolean default 1,
  primary key(materia_id, profesor_id),
  foreign key(materia_id)
  references materia(id)
  on delete cascade,
  foreign key(profesor_id)
  references profesor(id)
  on delete cascade
);
create table inscribe(
  id integer not null auto_increment,
  estudiante_id integer not null,
  curso_id integer not null,
  gestion_id integer not null,
  fecha date,
  primary key(id),
  foreign key(estudiante_id)
  references estudiante(id)
  on delete cascade,
  foreign key(curso_id)
  references curso(id)
  on delete cascade,
  foreign key(gestion_id)
  references gestion(id)
  on delete cascade
);
create table cursa(
  id integer not null auto_increment,
  curso_id integer not null,
  materia_id integer not null,
  primary key(id),
  foreign key(curso_id)
  references curso(id)
  on delete cascade,
  foreign key(materia_id)
  references materia(id)
  on delete cascade
);
create table horario(
  id integer not null auto_increment,
  cursa_id integer not null,
  periodo_id integer not null,
  gestion_id integer not null,
  primary key(id),
  foreign key(cursa_id)
  references cursa(id)
  on delete cascade,
  foreign key(periodo_id)
  references periodo(id)
  on delete cascade,
  foreign key(gestion_id)
  references gestion(id)
  on delete cascade
);
create table instruye(
  id integer not null auto_increment,
  profesor_id integer not null,
  cursa_id integer not null,
  gestion_id integer not null,
  primary key(id),
  foreign key(profesor_id)
  references profesor(id)
  on delete cascade,
  foreign key(cursa_id)
  references cursa(id)
  on delete cascade,
  foreign key(gestion_id)
  references gestion(id)
  on delete cascade
);
create table trabajo(
  id integer not null auto_increment,
  nombre varchar(255),
  fecha date,
  bimestre_id integer not null,
  instruye_id integer not null,
  primary key(id),
  foreign key(bimestre_id)
  references bimestre(id)
  on delete cascade,
  foreign key(instruye_id)
  references instruye(id)
  on delete cascade
);
create table estudiante_trabajo(
  estudiante_id integer not null,
  trabajo_id integer not null,
  nota tinyint,
  primary key(estudiante_id, trabajo_id),
  foreign key(estudiante_id)
  references estudiante(id)
  on delete cascade,
  foreign key(trabajo_id)
  references trabajo(id)
  on delete cascade
);
create table admin(
  id integer not null auto_increment,
  nombres varchar(127),
  appat varchar(127),
  apmat varchar(127),
  cel varchar(63),
  ci varchar(63),
  password varchar(127),
  primary key(id)
);
create table comunicado(
  id integer not null auto_increment,
  admin_id integer not null,
  fecha date,
  hora time,
  titulo varchar(63),
  cont text,
  remitente varchar(63),
  primary key(id),
  foreign key(admin_id)
  references admin(id)
  on delete cascade
);
-- Insertamos las materias
insert into materia (nombre, nombremin, campo) values
("REL", "Valores, Espiritualidad y Religiones", "Cosmos y Pensamiento"),
("LEN", "Comunicación y Lenguajes", "Comunidad y Sociedad"),
("SOC", "Ciencias Sociales", "Comunidad y Sociedad"),
("BIO", "Biología Geografía", "Vida, Tierra y Territorio"),
("A.P.", "Técnica, Tecnología General", "Ciencia, Tecnología y Producción"),
("MAT", "Matemática", "Ciencia, Tecnología y Producción"),
("T.V.", "Artes Plásticas y Visuales", "Comunidad y Sociedad"),
("ING", "Lengua Extrangera", "Comunidad y Sociedad"),
("E.F.", "Educación Física y Deportes", "Comunidad y Sociedad"),
("MUS", "Educación Musical", "Comunidad y Sociedad"),
("FIL", "Cosmovisiones Filosóficas y Psicología", "Cosmos y Pensamiento"),
("LIT", "Comunicación y Lenguajes", "Comunidad y Sociedad"),
("FI-QUI", "Física Química", "Vida, Tierra y Territorio"),
("FIS", "Física Química", "Vida, Tierra y Territorio");
insert into dia (literal) values
("Lunes"), ("Martes"), ("Miércoles"), ("Jueves"), ("Viernes"), ("Sábado");
insert into curso (nro, paralelo) values
(1, 'A'), (1, 'B'), (1, 'C'), (1, 'D'), (1, 'E'), (1, 'F'), (1, 'G'), (1, 'H'),
(2, 'A'), (2, 'B'), (2, 'C'), (2, 'D'), (2, 'E'), (2, 'F'), (2, 'G'), (2, 'H'),
(3, 'A'), (3, 'B'), (3, 'C'), (3, 'D'), (3, 'E'), (3, 'F'), (3, 'G'), (3, 'H'),
(4, 'A'), (4, 'B'), (4, 'C'), (4, 'D'), (4, 'E'), (4, 'F'), (4, 'G'), (4, 'H'),
(5, 'A'), (5, 'B'), (5, 'C'), (5, 'D'), (5, 'E'), (5, 'F'), (5, 'G'), (5, 'H'),
(6, 'A'), (6, 'B'), (6, 'C'), (6, 'D'), (6, 'E'), (6, 'F'), (6, 'G'), (6, 'H');
insert into bimestre (nro) values(1),(2),(3),(4);
insert into gestion (nro) values(2018);
insert into bimestre_gestion (gestion_id, bimestre_id, active) values (1, 1, 1);
insert into cursa (curso_id, materia_id) values
(1, 1),(1, 2),(1, 3),(1, 4),(1, 5),(1, 6),(1, 8),(1, 7),(1, 9),(1, 11),(1, 10),
(2, 1),(2, 2),(2, 3),(2, 4),(2, 5),(2, 6),(2, 8),(2, 7),(2, 9),(2, 11),(2, 10),
(3, 1),(3, 2),(3, 3),(3, 4),(3, 5),(3, 6),(3, 8),(3, 7),(3, 9),(3, 11),(3, 10),
(4, 1),(4, 2),(4, 3),(4, 4),(4, 5),(4, 6),(4, 8),(4, 7),(4, 9),(4, 11),(4, 10),
(5, 1),(5, 2),(5, 3),(5, 4),(5, 5),(5, 6),(5, 8),(5, 7),(5, 9),(5, 11),(5, 10),
(6, 1),(6, 2),(6, 3),(6, 4),(6, 5),(6, 6),(6, 8),(6, 7),(6, 9),(6, 11),(6, 10),
(7, 1),(7, 2),(7, 3),(7, 4),(7, 5),(7, 6),(7, 8),(7, 7),(7, 9),(7, 11),(7, 10),
(8, 1),(8, 2),(8, 3),(8, 4),(8, 5),(8, 6),(8, 8),(8, 7),(8, 9),(8, 11),(8, 10),

(9, 1),(9, 2),(9, 3),(9, 4),(9, 5),(9, 6),(9, 7),(9, 8),(9, 9),(9, 10),(9, 11),
(10, 1),(10, 2),(10, 3),(10, 4),(10, 5),(10, 6),(10, 7),(10, 8),(10, 9),(10, 10),(10, 11),
(11, 1),(11, 2),(11, 3),(11, 4),(11, 5),(11, 6),(11, 7),(11, 8),(11, 9),(11, 10),(11, 11),
(12, 1),(12, 2),(12, 3),(12, 4),(12, 5),(12, 6),(12, 7),(12, 8),(12, 9),(12, 10),(12, 11),
(13, 1),(13, 2),(13, 3),(13, 4),(13, 5),(13, 6),(13, 7),(13, 8),(13, 9),(13, 10),(13, 11),
(14, 1),(14, 2),(14, 3),(14, 4),(14, 5),(14, 6),(14, 7),(14, 8),(14, 9),(14, 10),(14, 11),
(15, 1),(15, 2),(15, 3),(15, 4),(15, 5),(15, 6),(15, 7),(15, 8),(15, 9),(15, 10),(15, 11),
(16, 1),(16, 2),(16, 3),(16, 4),(16, 5),(16, 6),(16, 7),(16, 8),(16, 9),(16, 10),(16, 11),

(17, 1),(17, 2),(17, 3),(17, 4),(17, 5),(17, 6),(17, 14),(17, 8),(17, 9),(17, 10),(17, 11),
(18, 1),(18, 2),(18, 3),(18, 4),(18, 5),(18, 6),(18, 14),(18, 8),(18, 9),(18, 10),(18, 11),
(19, 1),(19, 2),(19, 3),(19, 4),(19, 5),(19, 6),(19, 14),(19, 8),(19, 9),(19, 10),(19, 11),
(20, 1),(20, 2),(20, 3),(20, 4),(20, 5),(20, 6),(20, 14),(20, 8),(20, 9),(20, 10),(20, 11),
(21, 1),(21, 2),(21, 3),(21, 4),(21, 5),(21, 6),(21, 14),(21, 8),(21, 9),(21, 10),(21, 11),
(22, 1),(22, 2),(22, 3),(22, 4),(22, 5),(22, 6),(22, 14),(22, 8),(22, 9),(22, 10),(22, 11),
(23, 1),(23, 2),(23, 3),(23, 4),(23, 5),(23, 6),(23, 14),(23, 8),(23, 9),(23, 10),(23, 11),
(24, 1),(24, 2),(24, 3),(24, 4),(24, 5),(24, 6),(24, 14),(24, 8),(24, 9),(24, 10),(24, 11),

(25, 1),(25, 2),(25, 3),(25, 4),(25, 5),(25, 6),(25, 14),(25, 8),(25, 9),(25, 10),(25, 11),
(26, 1),(26, 2),(26, 3),(26, 4),(26, 5),(26, 6),(26, 14),(26, 8),(26, 9),(26, 10),(26, 11),
(27, 1),(27, 2),(27, 3),(27, 4),(27, 5),(27, 6),(27, 14),(27, 8),(27, 9),(27, 10),(27, 11),
(28, 1),(28, 2),(28, 3),(28, 4),(28, 5),(28, 6),(28, 14),(28, 8),(28, 9),(28, 10),(28, 11),
(29, 1),(29, 2),(29, 3),(29, 4),(29, 5),(29, 6),(29, 14),(29, 8),(29, 9),(29, 10),(29, 11),
(30, 1),(30, 2),(30, 3),(30, 4),(30, 5),(30, 6),(30, 14),(30, 8),(30, 9),(30, 10),(30, 11),
(31, 1),(31, 2),(31, 3),(31, 4),(31, 5),(31, 6),(31, 14),(31, 8),(31, 9),(31, 10),(31, 11),
(32, 1),(32, 2),(32, 3),(32, 4),(32, 5),(32, 6),(32, 14),(32, 8),(32, 9),(32, 10),(32, 11),

(33, 1),(33, 3),(33, 4),(33, 5),(33, 6),(33, 8),(33, 9),(33, 10),(33, 11),(33, 12),(33, 13),
(34, 1),(34, 3),(34, 4),(34, 5),(34, 6),(34, 8),(34, 9),(34, 10),(34, 11),(34, 12),(34, 13),
(35, 1),(35, 3),(35, 4),(35, 5),(35, 6),(35, 8),(35, 9),(35, 10),(35, 11),(35, 12),(35, 13),
(36, 1),(36, 3),(36, 4),(36, 5),(36, 6),(36, 8),(36, 9),(36, 10),(36, 11),(36, 12),(36, 13),
(37, 1),(37, 3),(37, 4),(37, 5),(37, 6),(37, 8),(37, 9),(37, 10),(37, 11),(37, 12),(37, 13),
(38, 1),(38, 3),(38, 4),(38, 5),(38, 6),(38, 8),(38, 9),(38, 10),(38, 11),(38, 12),(38, 13),
(39, 1),(39, 3),(39, 4),(39, 5),(39, 6),(39, 8),(39, 9),(39, 10),(39, 11),(39, 12),(39, 13),
(40, 1),(40, 3),(40, 4),(40, 5),(40, 6),(40, 8),(40, 9),(40, 10),(40, 11),(40, 12),(40, 13),

(41, 1),(41, 3),(41, 4),(41, 5),(41, 6),(41, 8),(41, 9),(41, 10),(41, 11),(41, 12),(41, 13),
(42, 1),(42, 3),(42, 4),(42, 5),(42, 6),(42, 8),(42, 9),(42, 10),(42, 11),(42, 12),(42, 13),
(43, 1),(43, 3),(43, 4),(43, 5),(43, 6),(43, 8),(43, 9),(43, 10),(43, 11),(43, 12),(43, 13),
(44, 1),(44, 3),(44, 4),(44, 5),(44, 6),(44, 8),(44, 9),(44, 10),(44, 11),(44, 12),(44, 13),
(45, 1),(45, 3),(45, 4),(45, 5),(45, 6),(45, 8),(45, 9),(45, 10),(45, 11),(45, 12),(45, 13),
(46, 1),(46, 3),(46, 4),(46, 5),(46, 6),(46, 8),(46, 9),(46, 10),(46, 11),(46, 12),(46, 13),
(47, 1),(47, 3),(47, 4),(47, 5),(47, 6),(47, 8),(47, 9),(47, 10),(47, 11),(47, 12),(47, 13),
(48, 1),(48, 3),(48, 4),(48, 5),(48, 6),(48, 8),(48, 9),(48, 10),(48, 11),(48, 12),(48, 13);

insert into hora values
(null, '08:00:00', '08:45:00'),
(null, '08:45:00', '09:30:00'),
(null, '09:30:00', '10:15:00'),
(null, '10:15:00', '11:00:00'),
(null, '11:20:00', '12:00:00'),
(null, '12:00:00', '12:40:00'),
(null, '12:40:00', '13:15:00');
insert into periodo (nro, dia_id, hora_id) values
(1, 1, 1),(2, 1, 2),(3, 1, 3),(4, 1, 4),(5, 1, 5),(6, 1, 6),(7, 1, 7),
(1, 2, 1),(2, 2, 2),(3, 2, 3),(4, 2, 4),(5, 2, 5),(6, 2, 6),(7, 2, 7),
(1, 3, 1),(2, 3, 2),(3, 3, 3),(4, 3, 4),(5, 3, 5),(6, 3, 6),(7, 3, 7),
(1, 4, 1),(2, 4, 2),(3, 4, 3),(4, 4, 4),(5, 4, 5),(6, 4, 6),(7, 4, 7),
(1, 5, 1),(2, 5, 2),(3, 5, 3),(4, 5, 4),(5, 5, 5),(6, 5, 6),(7, 5, 7),
(1, 6, 1),(2, 6, 2),(3, 6, 3),(4, 6, 4),(5, 6, 5),(6, 6, 6),(7, 6, 7);