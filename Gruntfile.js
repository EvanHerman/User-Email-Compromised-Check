'use strict';
module.exports = function(grunt) {

    grunt.initConfig({
	
        // js minification
        uglify: {
            dist: {
                files: {
					// admin scripts
                    'js/min/account-clean.min.js': [ // account clean script
                        'js/account-clean.js'
                    ],
					 'js/min/account-compromised.min.js': [ // account compramised script
                        'js/account-compromised.js'
                    ],
					 'js/min/remodal.min.js': [ // remodal - modal script
                        'js/remodal.js'
                    ],
                }
            }
        },

		// css minify all contents of our directory and add .min.css extension
		cssmin: {
			target: {
				files: [
					// admin css files
					{
						expand: true,
						cwd: 'css/',
						src: [
							'ecc-profile-styles.css',
							'remodal.css',
							'remodal-default-theme.css',
						], // main style declaration file
						dest: 'css/min/',
						ext: '.min.css'
					},
				]
			}
		},

        // watch our project for changes
       watch: {
			admin_css: { // admin css
				files: 'css/*.css',
				tasks: ['cssmin'],
				options: {
					spawn:false,
					event:['all']
				},
			},
			admin_js: { // admin js
				files: 'js/*.js',
				tasks: ['uglify'],
				options: {
					spawn:false,
					event:['all']
				},
			},
		},
		
		// Borwser Sync
		browserSync: {
			bsFiles: {
				src : [ 'css/min/*.min.css' , 'js/min/*.min.js' ],
			},
			options: {
				proxy: "localhost/wp-svg-2/",
				watchTask : true
			}
		},
		
		// Autoprefixer for our CSS files
		postcss: {
			options: {
                map: true,
                processors: [
                    require('autoprefixer-core')({
                        browsers: ['last 2 versions']
                    })
                ]
            },
			dist: {
			  src: [ 'css/*.css' ]
			}
		},
		
    });

    // load tasks
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-browser-sync'); // browser-sync auto refresh
	grunt.loadNpmTasks('grunt-postcss'); // CSS autoprefixer plugin (cross-browser auto pre-fixes)

    // register task
    grunt.registerTask('default', [
		'uglify',
        'cssmin',
		'postcss',
		'browserSync',
        'watch',
    ]);

};