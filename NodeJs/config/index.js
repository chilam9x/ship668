'use strict';

let _ = require('lodash');

let env = process.env.NODE_ENV || 'development';

module.exports = _.merge(
  require('./default'),
  require('./environments/' + env + '.js') || {});
