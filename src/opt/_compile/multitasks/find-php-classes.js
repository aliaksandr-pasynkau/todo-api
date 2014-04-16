"use strict";

module.exports = function (grunt) {

	return function () {
		var _ = require('lodash');
		var taskUtils = require('./_utils');

		var options = this.options({
			cwd: null,
			src: '**/*.php',
			beauty: false,
			outputJSON: null
		});

		options.cwd = (options.cwd || '').replace(/[\\\/]*$/, '/');

		var classMap = {};

		_.each(grunt.file.expand({ cwd: options.cwd }, options.src), function (filePath) {
			var content = grunt.file.read(options.cwd + filePath);
			if (/\s+(class|interface)\s*/.test(content)) {
				content.replace(/\n\s*(?:abstract|final)?\s*(class|interface)\s+([a-z0-9A-Z_]+)\s*/g, function ($0, $1, $2) {
					classMap[$2] = filePath;
					return $0;
				});
			}
		});

		grunt.file.write(options.outputJSON, JSON.stringify(classMap, null, options.beauty ? 4 : null));
		taskUtils.logFileOk(options.outputJSON);
	};

};