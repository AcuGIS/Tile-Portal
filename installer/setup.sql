CREATE TYPE public.userlevel AS ENUM ('Admin', 'User', 'Devel');

CREATE TABLE public.user (	id SERIAL PRIMARY KEY,
  name character varying(250),
  email character varying(250),
  password character varying(250),
  accesslevel public.userlevel,
	ftp_user character varying(250),
	pg_password character varying(250),
	owner_id integer NOT NULL	REFERENCES public.user(id),
	UNIQUE(email)
);

CREATE TABLE public.access_group (	id SERIAL PRIMARY KEY,
	name character varying(255) NOT NULL,
	owner_id integer NOT NULL	REFERENCES public.user(id),
	UNIQUE(name)
);

CREATE TABLE public.user_access (	id SERIAL PRIMARY KEY,
    user_id integer NOT NULL					REFERENCES public.user(id),
    access_group_id integer NOT NULL	REFERENCES public.access_group(id),
		UNIQUE(user_id, access_group_id)
);

CREATE TABLE public.pglink ( id SERIAL PRIMARY KEY,
	name character varying(250) NOT NULL,
	host character varying(250) NOT NULL,
	port integer NOT NULL default 5432,
	username character varying(250) NOT NULL,
  password character varying(250) NOT NULL,
	schema character varying(80) DEFAULT 'public',
	dbname character varying(80) NOT NULL,
	svc_name character varying(50) NOT NULL,
	owner_id integer NOT NULL	REFERENCES public.user(id),
	UNIQUE(name)
);

CREATE TABLE public.pglink_access (	id SERIAL PRIMARY KEY,
  pglink_id integer NOT NULL				REFERENCES public.pglink(id),
  access_group_id integer NOT NULL	REFERENCES public.access_group(id),
	UNIQUE(pglink_id, access_group_id)
);

CREATE TABLE public.service (	id SERIAL PRIMARY KEY,
	name character varying(250) NOT NULL,
	pglink_id integer NOT NULL	REFERENCES public.pglink(id),
	owner_id integer NOT NULL		REFERENCES public.user(id)
);

CREATE TABLE public.service_access (	id SERIAL PRIMARY KEY,
    service_id integer NOT NULL				REFERENCES public.service(id),
    access_group_id integer NOT NULL	REFERENCES public.access_group(id),
		UNIQUE(service_id, access_group_id)
);

CREATE TABLE public.layer (	id SERIAL PRIMARY KEY,
	name character varying(250) NOT NULL,
	public BOOLEAN DEFAULT False,
	svc_id integer NOT NULL	REFERENCES public.service(id),
	owner_id integer NOT NULL	REFERENCES public.user(id),
	UNIQUE(name)
);

CREATE TABLE public.layer_access (	id SERIAL PRIMARY KEY,
    layer_id integer NOT NULL					REFERENCES public.layer(id),
    access_group_id integer NOT NULL	REFERENCES public.access_group(id),
		UNIQUE(layer_id, access_group_id)
);