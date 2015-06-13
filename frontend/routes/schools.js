var express = require('express');
var router = express.Router();
var schools = require("../models/school.js");

/* GET users listing. */
router.get('/', function(req, res, next) {
  res.render('schools/filtering', {
    title: "Přehled škol",
    schools: schools.getAll()
  });
});


/* GET only the post HTML (suitable for AJAX). */
router.get('/get-detail/:id', function(req, res, next) {  
  res.render('schools/get-detail', {
    school: schools.get(req.params.id)
  });
});

/* GET detail page. */
router.get('/detail/:id', function(req, res, next) {  
  res.render('schools/detail', {
    title: "Detail školy",
    school: schools.get(req.params.id)
  });
});

module.exports = router;
