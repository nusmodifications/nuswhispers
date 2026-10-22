// Every VITE_* value is public and embedded in the browser bundle.
export const config = {
  apiUrl: (import.meta.env.VITE_API_URL || '/api').replace(/\/$/, ''),
  facebookPageId: import.meta.env.VITE_FB_PAGE_ID || '',
  fingerprintApiKey: import.meta.env.VITE_FINGERPRINT_API_KEY || '',
  fingerprintStorageKey: import.meta.env.VITE_FINGERPRINT_STORAGE_KEY || '',
  recaptchaKey: import.meta.env.VITE_RECAPTCHA_KEY || '',
};
