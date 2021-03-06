/*
 * Copyright (C) eZ Systems AS. All rights reserved.
 * For full copyright and license information view LICENSE file distributed with this source code.
 */
YUI.add('ez-richtext-resolveimage-tests', function (Y) {
    var processTest,
        Assert = Y.Assert, Mock = Y.Mock;

    processTest = new Y.Test.Case({
        name: "eZ RichText resolve image process test",

        setUp: function () {
            this.containerContent = Y.one('.container').getContent();
            this.processor = new Y.eZ.RichTextResolveImage();
            this.view = new Y.View({
                container: Y.one('.container'),
                field: {id: 42},
            });
            this.fields = {};
            this.fields["41"] = {'image': {}};
            this.fields["42"] = {'image': {}};
        },

        tearDown: function () {
            this.view.get('container').setContent(this.containerContent);
            this.view.destroy();
            delete this.view;
            delete this.processor;
        },

        "Should render the images as loading": function () {
            var image1 = this.view.get('container').one('#image1'),
                image2 = this.view.get('container').one('#image2');

            this.processor.process(this.view);

            Assert.isTrue(
                image1.hasClass('is-embed-loading'),
                "The image should get the loading class"
            );
            Assert.isTrue(
                image2.hasClass('is-embed-loading'),
                "The image should get the loading class"
            );
            Assert.areEqual(
                'p', image1.one('.ez-embed-content').get('localName'),
                "The image content element should have been added"
            );
            Assert.areEqual(
                'p', image2.one('.ez-embed-content').get('localName'),
                "The image content element should have been added"
            );
        },

        "Should ignore already rendered image": function () {
            var image = this.view.get('container').one('#image-loaded');

            this.processor.process(this.view);
            Assert.isFalse(
                image.hasClass('is-embed-loading'),
                "The already loaded image should be ignored"
            );
        },

        "Should ignore embed not representing images": function () {
            var notImage = this.view.get('container').one('#not-image');

            this.processor.process(this.view);
            Assert.isFalse(
                notImage.hasClass('is-embed-loading'),
                "embed not representing an image should be ignored"
            );
        },

        "Should search for the corresponding content": function () {
            var search = false;

            this.view.once('contentSearch', function (e) {
                search = true;

                Assert.areEqual(
                    "41,42",
                    e.search.criteria.ContentIdCriterion,
                    "The content should be loaded by id"
                );
                Assert.isTrue(
                    e.loadContent,
                    "The search should be configured to load the content"
                );
                Assert.isTrue(
                    e.loadContentType,
                    "The search should be configured to load the content type"
                );
            });
            this.processor.process(this.view);

            Assert.isTrue(search, "A search should be triggered");
        },

        _getContentMock: function (contentId) {
            var content = new Mock(),
                attrs = {contentId: contentId, name: "name-" + contentId, fields: this.fields[contentId]};

            Mock.expect(content, {
                method: 'get',
                args: [Mock.Value.String],
                run: function (attr) {
                    if ( attrs[attr] ) {
                        return attrs[attr];
                    }
                    Assert.fail("Unexpected call to get('" + attr + "')");
                },
            });
            return content;
        },

        _getContentTypeMock: function () {
            var type = new Mock();

            Mock.expect(type, {
                method: 'getFieldDefinitionIdentifiers',
                args: ['ezimage'],
                returns: ['image'],
            });
            return type;
        },

        "Should load the variations": function () {
            var content1 = this._getContentMock("41"),
                content2 = this._getContentMock("42"),
                type = this._getContentTypeMock(),
                loadImageVariation = 0;

            this.view.once('contentSearch', function (e) {
                e.callback.call(this, false, [{content: content1, contentType: type}, {content: content2, contentType: type}]);
            });
            this.view.on('loadImageVariation', Y.bind(function (e) {
                loadImageVariation++;
                if ( this.fields["41"].image === e.field ) {
                    Assert.areEqual(
                        "large", e.variation,
                        "The 'large' variation should be requested"
                    );
                } else if ( this.fields["42"].image === e.field ) {
                    Assert.areEqual(
                        "medium", e.variation,
                        "The 'medium' variation should be requested"
                    );
                } else {
                    Assert.fail("Unexpected field in loadImageVariation parameter");
                }
            }, this));
            this.processor.process(this.view);

            Assert.areEqual(
                2, loadImageVariation,
                "The loadImageVariation event should have been fired 2 times"
            );
        },

        "Should render images": function () {
            var image1 = this.view.get('container').one('#image1'),
                image2 = this.view.get('container').one('#image2'),
                content1 = this._getContentMock("41"),
                content2 = this._getContentMock("42"),
                type = this._getContentTypeMock(),
                uri = "http://www.reactiongifs.com/r/The-Hills.gif";

            this.view.once('contentSearch', function (e) {
                e.callback.call(this, false, [{content: content1, contentType: type}, {content: content2, contentType: type}]);
            });
            this.view.on('loadImageVariation', Y.bind(function (e) {
                e.callback.call(this, false, {uri: uri});
            }));
            this.processor.process(this.view);

            Assert.isFalse(
                image1.hasClass('is-embed-loading'),
                "The loading class should have been removed"
            );
            Assert.areEqual(
                "img", image1.one('.ez-embed-content').get('localName'),
                "The image should be rendered"
            );
            Assert.areEqual(
                uri, image1.one('.ez-embed-content').getAttribute('src'),
                "The image should be rendered"
            );
            Assert.areEqual(
                "name-41", image1.one('.ez-embed-content').getAttribute('alt'),
                "The image should be rendered"
            );
            Assert.isFalse(
                image2.hasClass('is-embed-loading'),
                "The loading class should have been removed"
            );
            Assert.areEqual(
                "img", image2.one('.ez-embed-content').get('localName'),
                "The image should be rendered"
            );
            Assert.areEqual(
                uri, image2.one('.ez-embed-content').getAttribute('src'),
                "The image should be rendered"
            );
            Assert.areEqual(
                "name-42", image2.one('.ez-embed-content').getAttribute('alt'),
                "The image should be rendered"
            );
        },
    });

    Y.Test.Runner.setName("eZ RichText resolve image processor tests");
    Y.Test.Runner.add(processTest);
}, '', {requires: ['test', 'view', 'ez-richtext-resolveimage']});
