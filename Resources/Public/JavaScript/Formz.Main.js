// Declaring namespace.
var Formz = (function() {
    /**
     * Contains the general configuration of Formz.
     *
     * @type {Object}
     */
    var configuration = {};

    /**
     * @type {string}
     */
    var ajaxUrl = '';

    return {
        TYPE_NOTICE: 'Notice',
        TYPE_WARNING: 'Warning',
        TYPE_ERROR: 'Error',

        /**
         * @param {Object} formzConfiguration
         */
        setConfiguration: function(formzConfiguration) {
            configuration = formzConfiguration;
        },

        /**
         * @returns {Object}
         */
        getConfiguration: function() {
            return configuration;
        },

        /**
         * @param {string} formzAjaxUrl
         */
        setAjaxUrl: function(formzAjaxUrl) {
            ajaxUrl = formzAjaxUrl;
        },

        /**
         * @returns {string}
         */
        getAjaxUrl: function() {
            return ajaxUrl;
        },

        /**
         * This function is called to debug anything, and will actually display
         * something only if the debug tool is activated (this is an option in
         * Formz extension configuration).
         *
         * @param {*}      value
         * @param {string} type
         */
        debug: function(value, type) {
            if (typeof Formz.Debug !== 'undefined') {
                Formz.Debug.debug(value, type);
            }
        }
    };
})();
