import axios from 'axios';
import camelCaseKeys from 'camelcase-keys';
import getConfig from 'next/config';

export default axios.create({
  baseURL: getConfig().publicRuntimeConfig.apiRoot,
  // Transforms response to have camelCase keys for OCD-ness.
  transformResponse: (data: any) =>
    camelCaseKeys(JSON.parse(data), { deep: true }),
});
