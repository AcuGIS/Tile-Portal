.. This is a comment. Note how any initial comments are moved by
   transforms to after the document title, subtitle, and docinfo.

.. demo.rst from: http://docutils.sourceforge.net/docs/user/rst/demo.txt

.. |EXAMPLE| image:: static/yi_jing_01_chien.jpg
   :width: 1em

************
Features
************

.. contents:: Table of Contents

You can enable pg_featureserv service for Layers.

pg_featureserv layers do not appear in the portal, but you can perform local queries as well as remote Public queries.

.. note::    

   Unless you plan to use this feature, do not enable the corresponding pg_featureserv service
  
Examples
=======================

List items for Public layer

.. code-block:: hrml

   https://domain.com/public.apiary/collections/public.area/items

Get json via Terminal using localhost
  
.. code-block:: sql

    wget -Oapiary.json "http://localhost:9006/collections/public.apiary/items?limit=2"

Would produce output to apiary.json as below

.. code-block:: json

    {
  {
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "id": "2",
      "geometry": {
        "type": "MultiPoint",
        "coordinates": [
          [
            9.259313172,
            46.811121062
          ]
        ]
      },
      "properties": {
        "area_id": 31,
        "average_harvest": 20,
        "bee_amount": "100000",
        "bee_species": "Apis Mellifera",
        "beekeeper": "Rita Levi Montalcini",
        "disease": true,
        "fid": 2,
        "field_uuid": "{d89d4955-0d11-43b9-a6e5-32add1729628}",
        "kind_of_disease": "EFB",
        "nbr_of_boxes": 10,
        "picture": "DCIM/4.jpg",
        "uuid": "{d89d4955-0d11-43b9-a6e5-32add1729628}"
      }
    },
    {
      "type": "Feature",
      "id": "4",
      "geometry": {
        "type": "MultiPoint",
        "coordinates": [
          [
            9.258077493,
            46.809587548
          ]
        ]
      },
      "properties": {
        "area_id": 30,
        "average_harvest": 10,
        "bee_amount": "10000",
        "bee_species": "Apis Mellifera",
        "beekeeper": "Stephen Hawking",
        "disease": false,
        "fid": 4,
        "field_uuid": "{d6a44bf1-9a33-4b4a-9b2d-9f88928aaf24}",
        "kind_of_disease": null,
        "nbr_of_boxes": 4,
        "picture": "DCIM/4.jpg",
        "uuid": "{d6a44bf1-9a33-4b4a-9b2d-9f88928aaf24}"
      }
    }
  ],
  "numberReturned": 2,
  "timeStamp": "2025-01-19T12:39:10Z",
  "links": [
    {
      "href": "https://quailpost.webgis1.com/public.apiary/collections/public.apiary/items",
      "rel": "self",
      "type": "application/json",
      "title": "This document as JSON"
    },
    {
      "href": "https://quailpost.webgis1.com/public.apiary/collections/public.apiary/items.html",
      "rel": "alternate",
      "type": "text/html",
      "title": "This document as HTML"
    }
    ]
  }
    

Port
=======================

Each instance of pg_featureserv runs on it's own assigned port.

Check the pg_featureserv table for the port of the service you are querying.















