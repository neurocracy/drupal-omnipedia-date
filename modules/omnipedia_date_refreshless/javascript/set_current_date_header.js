// -----------------------------------------------------------------------------
//   Omnipedia RefreshLess component to send the current date as a header
// -----------------------------------------------------------------------------
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

  component.addBehaviour(
    component.getName(),
    'omnipedia-refreshless-send-current-date',
    'html',
    function(context, settings) {

      $(this).on(`refreshless:before-fetch-request.${eventNamespace}`, (
        event,
      ) => {

        const date = settings.omnipedia.currentDate;

        const headerName = settings.omnipedia.setDateHeaderName;

        if (
          // This is currently primarily intended to search form submits,
          // because those are one of the few requests that we don't/can't
          // prefetch/preload and do not have a date in their URL so they get
          // out of sync constantly as links are prefetched/preloaded
          event.detail.isFormSubmit === false ||
          // Validate the date and header names to prevent fetch failures if
          // one of them was not output correctly by the back-end.
          typeof date !== 'string' ||
          date.length === 0 ||
          typeof headerName !== 'string' ||
          headerName.length === 0
        ) {
          return;
        }

        event.preventDefault();

        event.detail.fetchOptions.headers[headerName] = date;

        event.detail.resume();

      });

    },
    function(context, settings, trigger) {

      $(this).off(`.${eventNamespace}`);

    },
  );

});
