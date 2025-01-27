# TilePortal


### Installer

```bash
   git clone https://github.com/AcuGIS/TilePortal.git
   cd TilePortal
   ./installer/postgres.sh
   ./installer/pg-tile.sh
   ./installer/app-install.sh
```

Optionally, provision and SSL certificate using:

```bash
 apt-get -y install python3-certbot-apache
 certbot --apache --agree-tos --email hostmaster@${HNAME} --no-eff-email -d ${HNAME}
```

Default credentials

   - Email: admin@admin.com
   - Password: tile

### Docker (Not for Production Use)

```bash
git clone https://github.com/AcuGIS/TilePortal.git
$ cd TilePortal
$ ./installer/docker-install.sh
$ docker-compose pull

Before calling up set docker/public.env with values used on your machine!
$ docker-compose up

If you want to build from source, run next command.
$ docker-compose build
```

URL: http://yourdomain.com:8000

## Documentation

tile_portal Docs [Documentation](https://tile-portal.docs.acugis.com).


## License
Version: MPL 2.0

