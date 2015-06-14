/* global dojo */


$(function() {
  
  /**
   * Email subscribtion
   */ 
  $("body").on("click", "#subscribe", function() {
    var btn = $(this);
    var email = $("#subscribe-email");
    if(email.get(0).checkValidity()) {
      email.closest(".form-group").removeClass("has-error");
      btn.attr("disabled", "disabled");
      $.ajax({
        "method": "POST",
        "url": "/schools/subscribe",
        "data": {
          "email": email.val(),
          "school_id": $(this).data("school_id")
        },
        success: function(data) {
          if(data.hasOwnProperty("success") && data.success === true) {
            // now inform the user and hide the modal
            $("#subscribe-btn").addClass("btn-success").prepend("<span class='glyphicon glyphicon-ok'></span>&nbsp;");
            btn.html("Váš email byl přidán do seznamu k odběru");              
            setTimeout(function() {
              $("#subscribe-modal").modal("hide");
            }, 1500);
          } else {
            alert("Stala se chyba, váš email nemohl být uložen.");
            btn.removeAttr("disabled");
          }
        }, 
        error: function() {
          alert("Stala se neočekávaná chyba, váš email nemohl být uložen.");
          btn.removeAttr("disabled");
        }
      })
    } else {
      alert("Email je ve špatném formátu.");
      email.closest(".form-group").addClass("has-error");      
    }    
  });
      
  /**
   * Request editing of school information
   */ 
  $("body").on("click", "#request-editing", function() {
    var btn = $(this);
    var email = $("#request-editing-email");
    if(email.get(0).checkValidity()) {
      email.closest(".form-group").removeClass("has-error");
      btn.attr("disabled", "disabled");
      $.ajax({
        "method": "POST",
        "url": "/schools/request-editing",
        "data": {
          "email": email.val(),
          "school_id": $(this).data("school_id")
        },
        success: function(data) {
          if(data.hasOwnProperty("success") && data.success === true) {
            // now inform the user and hide the modal
            btn.html("Byl Vám zaslán email s přístupovým odkazem");
            $("#request-editing-btn").addClass("btn-success").prepend("<span class='glyphicon glyphicon-ok'></span>&nbsp;");                        
            setTimeout(function() {
              $("#request-editing-modal").modal("hide");
            }, 1500);
          } else {
            alert("Stala se chyba, nemohl Vám být odeslám email s přístupovým odkazem.");
            btn.removeAttr("disabled");
          }
        }, 
        error: function() {
          alert("Stala se neočekávaná chyba, email s přístupovým odkazem nebyl odeslán.");
          btn.removeAttr("disabled");
        }
      })
    } else {
      alert("Email je ve špatném formátu.");
      email.closest(".form-group").addClass("has-error");      
    }    
  });
  
  
  /**
   * Request school information ownership
   */ 
  $("body").on("click", "#claim", function() {
    var btn = $(this);
    var email = $("#claim-email");
    var msg = $("#claim-msg");
    if(email.get(0).checkValidity()) {
      email.closest(".form-group").removeClass("has-error");
      btn.attr("disabled", "disabled");
      $.ajax({
        "method": "POST",
        "url": "/schools/claim-ownership",
        "data": {
          "email": email.val(),
          "school_id": $(this).data("school_id"),
          "message": msg.val()
        },
        success: function(data) {
          if(data.hasOwnProperty("success") && data.success === true) {
            // now inform the user and hide the modal
            btn.html("V brzké době Vás budeme kontaktovat s dalšími informacemi a instrukcemi.");
            $("#claim-btn").addClass("btn-success").prepend("<span class='glyphicon glyphicon-ok'></span>&nbsp;");                        
            setTimeout(function() {
              $("#claim-modal").modal("hide");
            }, 1500);
          } else {
            alert("Stala se chyba, nemohl Vám být odeslám email s informacemi.");
            btn.removeAttr("disabled");
          }
        }, 
        error: function() {
          alert("Stala se neočekávaná chyba, email s přístupovým odkazem nebyl odeslán.");
          btn.removeAttr("disabled");
        }
      })
    } else {
      alert("Email je ve špatném formátu.");
      email.closest(".form-group").addClass("has-error");      
    }    
  });
  
  /**
   * Maps
   */
  var map;
  var geocoder;
  var points = [];
  
  require([
    "esri/map", "esri/geometry/Point", "esri/symbols/PictureMarkerSymbol", "esri/dijit/Geocoder", "esri/symbols/TextSymbol", "esri/symbols/SimpleFillSymbol", "esri/graphic", "esri/Color", "esri/symbols/Font", "esri/SpatialReference", "dojo/domReady!"
  ], function(
    Map, Point, PictureMarkerSymbol, Geocoder, TextSymbol, SimpleFillSymbol, Graphics, Color, Font, SpatialReference
  ) {
    
    $("#searchable-map").each(function() { // pseudohack - I don't want to read the documentation - but there is only one "#map" ;)
      // id of this map
      var mapEl = $(this);
      var id = mapEl.attr("id");
      if(!id) {
        console.log("one of the map doesn't have an ID - it can't be initialised", $(this));
        return;
      }
      
      map = new Map(id, {
        basemap: "streets",
        center: [ mapEl.data("lon"), mapEl.data("lat") ],
        zoom: 8
      });              
    });
    
    var graphicItems = [];
      
    function addPoint(name, type, point) {    
      var bg = new PictureMarkerSymbol("/images/mapbg.svg", Math.max(50, name.length * 7), 30);
      bg.setOffset(0, 0);
      map.graphics.add(new Graphics(point, bg));
      graphicItems.push(bg);
      
      var wedge = new PictureMarkerSymbol("/images/mapwedge.svg", 20, 10);
      bg.setOffset(0, 20);
      map.graphics.add(new Graphics(point, wedge));
      graphicItems.push(wedge);
      
      var text = new TextSymbol();
      text.setText(name);
      text.setFont(new Font("12px", "normal", "normal", "normal", "sans-serif"));
      text.setColor(new Color([255, 255, 255, 255]));
      text.setOffset(0, 15);
      map.graphics.add(new Graphics(point, text));
      graphicItems.push(text);
    }     
  
    $("body").on("click", "#filter", function() {
      $("#filter").attr("disabled", "disabled").text("Aktualizuji seznam škol...");
          
      setTimeout(function() {
        var search = $("#search-data");    
        $.ajax({
          "type": "GET",
          "url": "/schools/nearby",
          "data": {
            "address": $("#search").val()
          },
          "success": function(response) {
            
            clearSchoolList();
            removeMapPoints();
            
            if(response.location) {
              var foundPoint = new Point(response.location.lon, response.location.lat);
              map.centerAndZoom(foundPoint, 14);  
            }
            
            // create the dots on the map
            // and the list of nearby schools           
            var data = response.schools.items;
            for(var i = 0; i < data.length; i++) {
              var lon = data[i].general.position.lon;
              var lat = data[i].general.position.lat;
              
              addSchoolToList(data[i]);
              if(points.indexOf([lon, lat]) == -1) { // do not add the point if it already exists
                var pt = new Point(lon, lat, new SpatialReference({ wkid: 4326 }));
                addPoint(data[i].general.name, "school", pt);         
                points.push(lon, lat);
              }
            }
          },
          "complete": function() {
            $("#filter").text("Vyfiltrovat").removeAttr("disabled");
          } 
        });    
      }, 1000);              
    });
    
    function removeMapPoints() {
        for(var i = 0; i < graphicItems.length; ++i) {
          map.graphics.remove(graphicItems[i]);
        }
        graphicItems = [];
    }
    
    function clearSchoolList() {
      $("#schools").html(""); // delete everything! no mercy!
      $("#details-column .detail").each(function() {
        $(this).remove();
      });
    }
    
    function addSchoolToList(data) {
      var btn = $("<button>").addClass("school focus").data("id", data.id);
      var name = data.general.name;
      var website = "http://www.skola.cz";
      var address = "Za Řekou 2, Praha 110 00";
      var table = $(
          "<table>"
        + "<tr><td><img src='/images/spot.svg'></td><td>" + address +  "</td></tr>"
        + "<tr><td><img src='/images/computer.svg'></td><td><a href='" + website + "' target='_blank'>" + website + "</a></td></tr>"
        + "</table>"
      );      
      
      btn.append("<h2>" + name + "</h2>").append(table);
      $("#schools").append(btn);
    }
    
    
      /**
       * View the detail of a school.
       */
       $("body").on("click", "button.school", function() {
          var btn = $(this);      
          var wasFocused = btn.hasClass("focus");
          
          // all the others must loose focus first
          $("button.school.focus").each(function() {
              $(this).removeClass("focus");
          });
          
          var id = btn.data("id");
          var detail = $(".detail[data-id=" + id + "]");
          
          // hide the detail
          $(".detail.shown").fadeOut().removeClass("shown");
                  
          // reveal the map - but not the first time the user clicks on a button
          if(wasFocused && detail.length > 0) {
            $("#searchable-map").fadeIn();
            return;
          }
          
          // hide the map
          $("#searchable-map").fadeOut();
          
          // focus the button
          btn.addClass("focus");
          
          // load the detail and display it      
          var id = btn.data("id");
          var detail = $(".detail[data-id=" + id + "]");
          if(detail.length > 0) {
            detail.fadeIn().addClass("shown");
          } else {
            $.ajax({
              "type": "GET",
              "url": "/schools/get-detail-" + id,
              "success": function(data) {
                  detail = $("<div>").addClass("detail shown");
                  detail.attr("data-id", id);
                  detail.html(data);
                  $("#details-column").append(detail);
                  detail.css({ top: btn.position().top - detail.find(".underlined-title").outerHeight() - parseInt(detail.find(".school-detail").css("margin-top")) - 2 + "px" });
                  var minHeight = Math.max($("#details-column").height(), detail.height() + detail.position().top);
                  $("#details-column").css("min-height", minHeight + 50 + "px"); // 50 - to make some spare space
                  
                  // now hide and then let fade in the detail            
                  detail.css("display", "none");
                  detail.fadeIn();
                }
              });
            }
        });
       
         /**
          * Reset the view
          */
         $("body").on("click", "#show-map", function() {
            // all the others must loose focus first
            $("button.school.focus").each(function() {
                $(this).removeClass("focus");
            });
            
            // hide the detail
            $(".detail.shown").fadeOut().removeClass("shown");
            
            // show the map!
            $("#searchable-map").fadeIn();
         });   
            
         /**
          * Filtering - dropdown
          */
          
         $("body").on("click", "button#toggle-settings", function() {
            $("#advanced-filtering").slideToggle();
         });
            
         
         // run "empty" filtering when the user comes to the page
         var search = $("#search-data");
         if(!search.data("address") || search.data("address").length === 0) {
           // no address - show the settings so the user knows, what to do
           $("#toggle-settings").click();
         } else {
           var address = search.data("address");
           var input = $("#search input");
           input.val(address); // prefill the address
           
           $("#filter").click();
         }  
    });
});