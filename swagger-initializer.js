window.onload = function() {
  //<editor-fold desc="Changeable Configuration Block">

  const ServerMutatorPlugin = (system) => ({
    rootInjects: {
      setServers: (servers) => {
        const jsonSpec = system.getState().toJSON().spec.json;
        const newJsonSpec = Object.assign({}, jsonSpec, { servers });

        return system.specActions.updateJsonSpec(newJsonSpec);
      }
    }
  });

  // the following lines will be replaced by docker/configurator, when it runs in a docker-container
  window.ui = SwaggerUIBundle({
    url: "https://raw.githubusercontent.com/bmlt-enabled/bmlt-root-server/main/src/storage/api-docs/api-docs.json",
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl,
      ServerMutatorPlugin
    ],
    layout: "StandaloneLayout",
    onComplete: () => {
      window.ui.setServers([
        {url: 'https://latest.aws.bmlt.app/main_server/', description: 'Latest'},
        {url: 'https://unstable.aws.bmlt.app/main_server/', description: 'Unstable'},
        {url: 'https://gyro.aws.bmlt.app/main_server/', description: 'Gyro'},
        {url: 'https://{domain}', description: 'Custom', variables: { domain: { default: '' } } },
      ]);
    }
  });

  //</editor-fold>
};
