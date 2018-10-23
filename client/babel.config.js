module.exports = api => {
  const config = { presets: [] };

  config.presets.push(
    api.env('test')
      ? [
          'next/babel',
          {
            'preset-env': {
              modules: 'commonjs',
            },
          },
        ]
      : 'next/babel',
  );

  config.presets.push('@zeit/next-typescript/babel');

  return config;
};
