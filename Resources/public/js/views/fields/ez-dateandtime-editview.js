/*
 * Copyright (C) eZ Systems AS. All rights reserved.
 * For full copyright and license information view LICENSE file distributed with this source code.
 */
YUI.add('ez-dateandtime-editview', function (Y) {
    "use strict";
    /**
     * Provides the field edit view for the date and time fields
     *
     * @module ez-dateandtime-editview
     */

    Y.namespace('eZ');

    var FIELDTYPE_IDENTIFIER = 'ezdatetime';

    /**
     * Date and time edit view
     *
     * @namespace eZ
     * @class DateAndTimeEditView
     * @constructor
     * @extends eZ.FieldEditView
     */
    Y.eZ.DateAndTimeEditView = Y.Base.create('dateAndTimeEditView', Y.eZ.FieldEditView, [], {
        events: {
            '.ez-dateandtime-date-input-ui input': {
                'blur': 'validate',
                'valuechange': 'validate',
            },
            '.ez-dateandtime-time-input-ui input': {
                'blur': 'validate',
                'valuechange': 'validate',
            }
        },

        /**
         * Validates the current input of date and time field
         *
         * @method validate
         */
        validate: function () {
            var dateValidity = this._getDateInputValidity(),
                timeValidity = this._getTimeInputValidity();

            if ( dateValidity.valueMissing || timeValidity.valueMissing  ) {
                this.set('errorStatus', 'This field is required');
            }
            else if ( dateValidity.badInput || timeValidity.badInput ) {
                this.set('errorStatus', 'This is not a valid input');
            } else {
                this.set('errorStatus', false);
            }
        },

        /**
         * Defines the variables to import in the field edit template for date and time
         *
         * @protected
         * @method _variables
         * @return {Object} containing isRequired
         */
        _variables: function () {
            var def = this.get('fieldDefinition'),
                field = this.get('field'),
                date,
                time;

            if (field && field.fieldValue && field.fieldValue.timestamp) {
                date = Y.Date.format(new Date(field.fieldValue.timestamp * 1000));
                time = Y.Date.format(new Date(field.fieldValue.timestamp * 1000), {format:"%T"});
            } else {
                date = new Date();
                time = new Date();
            }

            return {
                "isRequired": def.isRequired,
                "html5InputDate": date,
                "html5InputTime": time
            };
        },

        /**
         * Returns the input validity state object for the input generated by
         * the date of the date and time template
         *
         * See https://developer.mozilla.org/en-US/docs/Web/API/ValidityState
         *
         * @protected
         * @method _getInputValidity
         * @return {ValidityState}
         */
        _getDateInputValidity: function () {
            return this.get('container').one('.ez-dateandtime-date-input-ui input').get('validity');
        },

        /**
         * Returns the input validity state object for the input generated by
         * the time of the date and time template
         *
         * See https://developer.mozilla.org/en-US/docs/Web/API/ValidityState
         *
         * @protected
         * @method _getInputValidity
         * @return {ValidityState}
         */
        _getTimeInputValidity: function () {
            return this.get('container').one('.ez-dateandtime-time-input-ui input').get('validity');
        },

        /**
         * Returns the currently filled date and time value
         *
         * @protected
         * @method _getFieldValue
         * @return {Object}
         */
        _getFieldValue: function () {
            var valueOfDateInput,
                valueOfTimeInput,
                container = this.get('container');

            valueOfDateInput = this.get('container').one('.ez-dateandtime-date-input-ui input').get('valueAsNumber');
            valueOfTimeInput = this.get('container').one('.ez-dateandtime-time-input-ui input').get('valueAsNumber');

            return {timestamp: ( valueOfDateInput + valueOfTimeInput )/1000};
        },
    });

    Y.eZ.FieldEditView.registerFieldEditView(FIELDTYPE_IDENTIFIER, Y.eZ.DateAndTimeEditView);
});