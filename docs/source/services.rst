**********************
Services
**********************

.. contents:: Table of Contents
Overview
==================

pg_tileserv is run as a service.

To access pg_tileserv services, click the Services link on the left menu

.. image:: _static/mapproxy-1.png


Restart
================

To stop/start/restart MapProxy, click the Stop or Restart button as shown below.

.. image:: _static/mapproxy-restart.png

Edit
================

To edit the mapproxy.yaml file, click the edit button as shown below.

.. image:: _static/mapproxy-edit.png

This will open the mapporxy.yaml file for editing.

.. image:: _static/mapproxy-edit-2.png

.. note::
    Be sure to click the Submit button at bottom after making changes.

MapProxy Directory
================

The MapProxy config directory is located at::

        /var/www/data/mapproxy

The default configuration files are shown below

.. image:: mapproxy-files.png


Cache Directory
================

The MapProxy config directory is located at::

        /var/www/data/mapproxy/cache_data

The ouput from the demo data is shown below

.. image:: maproxy-cache-directory.png


Authentication
================

When a Layer is set to Private, MapProxy authenticates requests against the QeoSerer user database.

Authentication is accomplished using the wsgiapp_authorize.patch file::

	patch -d /usr/lib/python3/dist-packages/mapproxy -p0 < installer/wsgiapp_authorize.patch

This file is located in the QeoServer installer directory.

Service Versioning
==================

Each update to the yaml file for each layer creates a restorable backup.

If you wish to restore a previous version, simply select it from the dropdown as show below

.. image:: seed-editor.png


Service File
=================

pg_tileserv is configured to run as a systemd service.

The pg_tileserv@.service file contains below by default::

	[Unit]
  Description=PG TileServ
  After=multi-user.target

  [Service]
  User=pgis
  WorkingDirectory=/opt/pg_tileserv
  Type=simple
  Restart=always
  ExecStart=/opt/pg_tileserv/pg_tileserv --config /opt/pg_tileserv/config/pg_tileserv%i.toml

  [Install]
  WantedBy=multi-user.target







