INSERT INTO public.user
	(name, email, password, accesslevel, ftp_user, pg_password, owner_id)
VALUES
	('Admin', 'admin@admin.com', 'ADMIN_APP_PASS', 'Admin', 'admin1', 'ADMIN_PG_PASS', 1);
	
INSERT INTO public.access_group
	(name, owner_id)
VALUES
	('Default', 1);

INSERT INTO public.user_access
	(user_id, access_group_id)
VALUES
	(1, 1);

INSERT INTO public.pglink
	(name,host,port,username,password,schema,dbname,svc_name,owner_id)
VALUES
	('countries', 'localhost', '5432', 'admin1', 'ADMIN_PG_PASS', 'public', 'countries', 'countries', 1);

INSERT INTO public.pglink_access
	(pglink_id, access_group_id)
VALUES
	(1, 1);

INSERT INTO public.service
	(name, pglink_id, owner_id)
VALUES
	('countries', '1', '1');

INSERT INTO public.service_access
	(service_id, access_group_id)
VALUES
	(1, 1);
	
INSERT INTO public.layer
	(name, public, svc_id, owner_id)
VALUES
	('public.countries', 'f', '1', '1');

INSERT INTO public.layer_access
	(layer_id, access_group_id)
VALUES
	(1, 1);
