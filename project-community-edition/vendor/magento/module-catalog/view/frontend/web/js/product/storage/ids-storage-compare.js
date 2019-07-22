/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'ko',
    'mageUtils',
    'Magento_Customer/js/customer-data',
    'Magento_Catalog/js/product/storage/ids-storage'
], function (_, ko, utils, customerData, idsStorage) {
    'use strict';

    return _.extend(utils.copy(idsStorage), {

        /**
         * Class name
         */
        name: 'IdsStorageCompare',

        /**
         * Initializes class
         *
         * @return Chainable.
         */
        initialize: function () {
            if (!this.data) {
                this.data = ko.observable({});
            }

            if (this.provider) {
                this.providerDataHandler(customerData.get(this.provider)());
                this.initProviderListener();
            }

            this.initLocalStorage()
                .cachesDataFromLocalStorage()
                .initDataListener();

            return this;
        },

        /**
         * Initializes listener for external data provider
         */
        initProviderListener: function () {
            customerData.get(this.provider).subscribe(this.providerDataHandler.bind(this));
        },

        /**
         * Initializes handler for external data provider update
         *
         * @param {Object} data
         */
        providerDataHandler: function (data) {
            data = data.items || data;
            data = this.prepareData(data);

            this.add(data);
        },

        /**
         * Prepares data to correct interface
         *
         * @param {Object} data
         *
         * @returns {Object} data
         */
        prepareData: function (data) {
            var result = {};

            _.each(data, function (item) {
                result[item.id] = {
                    'added_at': new Date().getTime() / 1000,
                    'product_id': item.id
                };
            });

            return result;
        }
    });
});
