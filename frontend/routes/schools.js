var express = require('express');
var router = express.Router();
var schools = require("../models/school.js");

/* GET users listing. */
router.get('/', function(req, res, next) {  
  schools.getAll([], req.query.address || undefined, function (data, aggregations, location) {
    res.render('schools/filtering', {
      title: "Přehled škol",
      schools: data, // init with empty list
      aggregations: aggregations,
      address: req.query.address,
      schoolType: req.query.school_type || "",
      location: location || { lon: 14.4031756, lat: 50.0881128 }
    });
  })
});

/* GET only the post HTML (suitable for AJAX). */
router.get('/get-detail-:id', function(req, res, next) {
  schools.get(req.params.id, function(school) {
    if(school === null) {
      next();
      return;
    }
    
    res.render('schools/get-detail', {
      school: school 
    });  
  });    
});

/* GET detail page. */
router.get('/detail-:id', function(req, res, next) {
  schools.get(req.params.id, function(school) {  
    if(school === null) {
      next(); // pass to another handler = eventually 404
      return;
    }
    
    res.render('schools/detail', {
      title: "Detail školy",
      school: school 
    });  
  });
});


var api = require("../models/api");

// Subscribe to get information from a school
router.get("/subscribe", function(req, res, next) {
    api.subscribe(req.query.school_id, req.query.email, function(success) {
        res.send({
          "success": success
        })
    });
});

// Unsubscribe from getting information from a school
router.get("/unsubscribe", function(req, res, next) {
    api.unsubscribe(req.query.school_id, req.query.email, req.query.token, function(success) {
        if(success) {
          res.redirect("/schools/unsubscribed");
        } else {
          res.redirect("/schools/unsubscribe-failed");
        }
    });
});

router.get("/unsubscribed", function(req, res, next) {
  res.render("schools/unsubscribed", {
    title: "Odběr informací byl zrušen"
  });
});

router.get("/unsubscribe-failed", function(req, res, next) {
  res.render("schools/unsubscribe-failed", {
    title: "Odběr informací bohužel nebyl zrušen"
  });
});

// Request editing of information of a school
router.post("/request-editing", function(req, res, next) {
    api.requestEdit(req.query.school_id, req.query.email, function(success) {
        res.send({
          "success": success
        })
    });
});

// Subscribe to get information from a school
router.post("/claim-ownership", function(req, res, next) {
    api.claimOwnership(req.query.school_id, req.query.email, req.query.message, function(success) {
        res.send({
          "success": success
        })
    });
});


// filter schools
router.get("/nearby", function(req, res, next) {
    schools.getAll(req.query.filters || [], req.query.address, function(data, aggregations, location) {
      res.send({
          "schools": data,
          "aggregations": aggregations,
          "location": location
      });
    });
});


module.exports = router;