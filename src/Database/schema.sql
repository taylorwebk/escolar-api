-- ESTUDIANTE, APODERADO, DOCENTE, REGENTE
-- ADMINISTRADOR, MATERIA, CURSO, TRABAJO, BIMESTRE
/*
apoderado: tabla para almacenar a los apoderados de los
estudiantes inscritos
*/
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
  primary key(id)
);
create table periodo(
  id integer not null auto_increment,
  nro integer,
  ini time,
  fin time,
  primary key(id)
);
create table profesor(
  id integer not null auto_increment,
  nombres varchar(127),
  apmat varchar(127),
  appat varchar(127),
  ci varchar(63),
  dir varchar(127),
  primary key(id)
);
create table inscribe(
  id integer not null auto_increment,
  curso_id integer not null,
  gestion_id integer not null,
  fecha date,
  primary key(id),
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
  gestion_id integer not null,
  bimestre_id integer not null,
  instruye_id integer not null,
  primary key(id),
  foreign key(gestion_id)
  references gestion(id)
  on delete cascade,
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
-- Insertamos las materias
insert into materia (nombre) values
("Religión"),
("Lenguaje"),
("Ciencias Sociales"),
("Biología"),
("Artes Plásticas"),
("Matemáticas"),
("Técnica Vocacional"),
("Inglés"),
("Educación Física"),
("Música"),
("Filosofía"),
("Literatura"),
("Física Química");
