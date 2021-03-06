"use strict";

module.exports = function(grunt){

    var _ = require('lodash');
    var markdown = require( "markdown" ).markdown;
    var handlebars = require("handlebars");

    var options = {
        expand: true,
        cwd: process.cwd() + "/src/client/content/",
        src: [ "**/*.md" ],
        dest: process.cwd() + "/build/client/content/",
        ext: "html",
        template: "template.hbs"
    };

    grunt.task.registerTask('client-content-compile', function(){

        var fs = grunt.file.expand({cwd: options.cwd}, options.src);

        _.each(fs, function(filePath) {
            var lang = filePath.split(/[\\\/]+/)[0];
            var blockName = filePath.split(/[\\\/]+/)[1];
            var fileName = filePath.split(/[\\\/]+/).pop();
            var content = markdown.toHTML(grunt.file.read(options.cwd + filePath));

            var template = handlebars.compile(grunt.file.read(options.cwd + options.template));
            var data = {center : content};

            var html = template(data);
            html = html.replace(/src\s*=\s*['"]\s*([^'"]+)\s*['"]/g, function($0, path){
                path = options.cwd + lang + "/" + path;
                return 'src="'+path+'"';
            });
            grunt.file.write(options.dest + lang + "/index.html", html);
            grunt.log.ok("File " + options.dest + lang + "/index.html compiled!");
        });
    });
};