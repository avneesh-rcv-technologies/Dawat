const publishBackendSyncState = (payload = {}) => {
  window.dispatchEvent(new CustomEvent('backend:sync-state', {
    detail: {
      module: 'Reports',
      ...payload,
    },
  }));
};

const channelTokenMeta = document.querySelector('meta[name="channel-token"]');

if (channelTokenMeta && channelTokenMeta.content) {
  const channelName = `user.${channelTokenMeta.content}.report-logs`;

  console.log("Subscribing to Echo channel:", channelName);
  window.Echo.private(channelName)
      .listen('.report-logged', (e) => {
          console.log("Event status:", e.data?.status, e.data?.sync_state);

          publishBackendSyncState({
            status: e.data?.status,
            sync_state: e.data?.sync_state,
            data: e.data,
          });
        //   updatePostProgress(e.data); 
      }) 
      .error((error) => {
          console.error('Echo subscription error:', error);
      });

  // Export event handling is now managed by exports.js (ExportManager)
  // The ExportManager will initialize automatically when exports.js is loaded
} else {
  console.error("Channel token not available for private channel subscription");
}
