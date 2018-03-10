'use strict';

angular.module('image.text')

.directive('edFontsPagination', ['$rootScope', 'fonts', function($rootScope, fonts) {

    return {
        restrict: 'A',
        link: function($scope, el) {

            //initiate pagination plugin
            el.pagination({
                items: 0,
                itemsOnPage: fonts.paginator.perPage,
                onPageClick: function(num) {
                    $scope.$apply(function() {
                        fonts.paginator.selectPage(num);
                    })
                }
            });

            //redraw pagination bar on total items change
            $scope.$watch('fonts.paginator.totalItems', function(value) {
                if (value) { el.pagination('updateItems', value) }
            });
        }
    }
}])

.factory('fonts', ['$rootScope', '$http', '$timeout', '$filter', 'localStorage', function($rootScope, $http, $timeout, $filter, localStorage) {

    var fonts = {

        loading: false,

        paginator: {

            /**
             * All available fonts.
             *
             * @type array
             */
            sourceItems: ["Slabo 27px",
                            "Roboto",
                            "Saira" ,
                            "Open Sans",
                            "Times New Roman",
                            "Playfair Display",
                            "Monotype Corsiva",
                            "PT Serif",
                            "IM Fell Double Pica SC",
                            "Cormorant Unicase",
                            "Chewy",
                            "Limelight",
                            "Poiret One",
                            "Voltaire",
                            "Oswald",
                            "Sniglet",
                            "Crushed",
                            //"Bookman Old Style",
                            "Arial",
                            "Helvetica",
                            "Lobster",
                            //"Disney",
                            "Star Trek",
                            //"Garamond",
                            "PT Sans",
                            "Indie Flower",
                            "Fjalla One",
                            "Pacifico",
                            "Amatic SC",
                            "Great Vibes",
                            "Permanent Marker",
                            "Bad Script",
                            "Monoton",
                            "Coming Soon",
                            "Just Another Hand",
                            "Delius Swash Caps",
                            "Rochester",
                            "Elsie Swash Caps",
                            "Inconsolata",
                            "Trocchi",
                            "Creepster",
                            "Bungee",
                            "Black Ops One",
                        ],

            /**
             * All available fonts with filtrs applied.
             */
            filteredItems: [],

            /**
             * Fonts currently being shown.
             *
             * @type array
             */
            currentItems: [],

            /**
             * Fonts to show per page.
             *
             * @type int
             */
            perPage: 10,

            /**
             * Total number of fonts.
             *
             * @type int
             */
            totalItems: 0,

            /**
             * Slice items for the given page.
             *
             * @param  {int} page
             * @return void
             */
            selectPage: function(page) {
                this.currentItems = this.filteredItems.slice(
                    (page-1)*this.perPage, (page-1)*this.perPage+this.perPage
                );

                //fonts.load();
            },

            filter: function(filters) {
                this.start($filter('filter')(fonts.paginator.sourceItems, filters));
            },

            /**
             * Start the paginator with given items.
             *
             * @param  {array} items
             * @return void
             */
            start: function(items) {
                console.log(items);
                this.perPage = items.length;
                this.filteredItems  = items;
                this.totalItems   = items.length;
                this.currentItems = items.slice(0, this.perPage);

                fonts.load(items);
            }
        },

        /**
         * Fetch all available fonts from GoogleFonts API.
         *
         * @return void
         */
        getAll: function(filters) {
            var self   = this,
                cached = localStorage.get('googleFonts'),
                key    = $rootScope.keys['google_fonts'];
            // console.log(fonts.paginator.currentItems);
            // if (cached) {
            //     self.paginator.sourceItems = cached;
            //     return filters ? self.paginator.filter(filters) : self.paginator.start(cached);
            // }

            // $http.get('https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key='+key)
            //     .success(function(data) {
            //         localStorage.set('googleFonts', data.items);
            //         self.paginator.sourceItems = data.items;
            //         filters ? self.paginator.filter(filters) : self.paginator.start(data.items);
            //     });
            //console.log(self.paginator.sourceItems);
            //localStorage.set('googleFonts', self.paginator.sourceItems);
            //filters ? self.paginator.filter(filters) : self.paginator.start(self.paginator.sourceItems);
            self.paginator.start(self.paginator.sourceItems);
        },

        /**
         * Load given google fonts into the DOM.
         *
         * @param  {mixed} names
         * @return void
         */
        load: function(names) {
            var head = $('head');
            $rootScope.loading = true;

            //make an array of font names from current fonts
            //in the paginator if none passed
            if ( ! names) {
                names = $.map(fonts.paginator.currentItems, function(font) {
                    console.log(font);
                    //return font.family;
                    return font;
                });

                //normalize names to array if string passed
            } else if ( ! angular.isArray(names)) {
                names = [names];
            }

            //remove previous page fonts
            //$(head).find('#dynamic-fonts').remove();
            names = names.filter(val => val !== "Times New Roman");
            names = names.filter(val => val !== "Helvetica");
            names = names.filter(val => val !== "Monotype Corsiva");
            var perPage = 17;
            //load the given fonts
            for( var i=0; i*perPage < names.length; i++){
                var subfonts = names.slice(i*perPage, (i+1)*perPage);
                head.append(
                    '<link rel="stylesheet" id="dynamic-fonts'+i+'" href="https://fonts.googleapis.com/css?family='+
                    subfonts.join('|').replace(/ /g, '+')+'">'
                );
            }
        },

        createLinkToFont: function(font) {
            var name = font.replace(/ /g, '+'),
                link = $('#'+name);
                
            if (link[0]) {
                link.attr('href', 'https://fonts.googleapis.com/css?family='+name);
            } else {
                $('head').append('<link rel="stylesheet" id="'+name+'" href="https://fonts.googleapis.com/css?family='+name+'">');
            }
        }
    };

    return fonts;
}]);