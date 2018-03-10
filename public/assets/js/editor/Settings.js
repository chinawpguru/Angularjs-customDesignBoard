'use strict';

angular.module('ImageEditor').factory('settings', ['$rootScope', '$http', function($rootScope, $http) {

	var settings = {
        all: {},

        get: function(name) {
            return this.all[name];
        }
	}

    $http.get('/wp-admin/admin-ajax.php?action=wineshop_label_get_photos_and_stickers').success(function(data) {
        settings.all = data;

        $rootScope.$emit('settings.ready');
    });

	return settings;
}]);