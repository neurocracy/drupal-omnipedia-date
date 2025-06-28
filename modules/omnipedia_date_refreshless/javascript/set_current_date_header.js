// -----------------------------------------------------------------------------
//   Omnipedia RefreshLess component to send the current date as a header
// -----------------------------------------------------------------------------
AmbientImpact.on(['fastdom',], function(aiFastDom) {
AmbientImpact.addComponent(
  'OmnipediaRefreshlessSetCurrentDateHeader',
(component, $) => {

  'use strict';

  /**
   * Event namespace name.
   *
   * @type {String}
   */
  const eventNamespace = component.getName();

  /**
   * FastDom instance.
   *
   * @type {FastDom}
   */
  const fastdom = aiFastDom.getInstance();

  component.addBehaviour(
    component.getName(),
    'omnipedia-refreshless-send-current-date',
    'body',
    async function(context, settings) {

      // @todo Output the current date to drupalSettings.omnipedia and use that.
      const $date = $('.omnipedia-header__current-date', context);

      const date = await fastdom.measure(() => $date.attr('datetime'));

      $(this)
      .on(`refreshless:before-fetch-request.${eventNamespace}`, (event) => {

        if (
          event.detail.isPrefetch === true ||
          event.detail.isPreload === true ||
          typeof date === 'undefined'
        ) {
          return;
        }

        event.preventDefault();

        event.detail.fetchOptions.headers[
          'X-Omnipedia-Set-Current-Date'
        ] = date;

        event.detail.resume();

      });

    },
    function(context, settings, trigger) {

      if (trigger !== 'unload') {
        return;
      }

      $(this).off(`refreshless:before-fetch-request.${eventNamespace}`);

    },
  );

});
});
