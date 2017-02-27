CONTENTS OF THIS FILE
---------------------

 * About Varnish Purge
 * Install
 * Server Side Configuration
 * Local Settings

ABOUT VARNISH PURGE
--------------------
This module is provide the UI interface for varnish purge.With the help of this module you can easily purge or ban your varnish caches.

INSTALL
------------
Install the module Varnish Purge in the usual way

SERVER SIDE CONFIGURATION
--------------------------
To work with this module,you have to make some changes on your varnish server.
1. Edit varnish.vcl file.

2. Make entry of your IP in this block.This is allowed your IP to make clear the
    varnish purge.
    acl purgers {
      "localhost";
      #"ip.ip.ip.ip";
    }

3.  Make sure following entries should be exist in the files

  sub vcl_recv {
    if (req.method == "PURGE") {
      if (!client.ip ~ purgers) {
              return (synth(405, "Method not allowed"));
      }
      if(req.http.host && req.url) {
        ban("req.http.host == " +req.http.host+" && req.url ~ "+req.url);
        return (synth(200, "Ban added"));
      }
      #return (purge);
      #return (hash);
    }
    if (req.method == "BAN") {
      if (!client.ip ~ purgers) {
        return (synth(405, "Method not allowed"));
      }
      if (req.http.x-ban-url && req.http.x-ban-host) {
              ban("obj.http.x-url ~ " + req.http.x-ban-url + " && obj.http.x-host ~ " + req.http.x-ban-host);
              return (synth(200, "Banned."));
      }
      if (req.http.x-ban-host) {
              ban("obj.http.x-host ~ " + req.http.x-ban-host);
              return (synth(200, "Banned."));
      }
    }
  }


LOCAL SETTINGS
----------------
You can find following options on this admin path 'admin/config/development/performance'.

i) Add Domain
   Add your domain for which you want to purge or ban your varnish cache

ii) Clear All Cache
  With the help of this button you can purge all varnish for registerd domain in previous option.

iii) Manual Ban
  You can Ban a type of pattern
  Eg. /abc/A, /abc/B, /abc/C ...
  For this type of pattern, you have to just enter
  /abc/*

iv) Manual Purge
  For individual page, you can use this option.
