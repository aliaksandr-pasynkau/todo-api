'use strict';

module.exports = function (grunt) {
	var opt = this,
		NAME = this.lnk(),
		SRC = this.lnk(opt.SRC),
		BUILD = this.lnk(opt.BUILD);

	this.include([
		this.lnk(null, 'install'),
		this.lnk(null, 'build')
	]);

};