import { useEffect, useEffectEvent, useRef, useState } from 'react';
import { config } from '../../lib/config';

type Recaptcha = {
  ready: (callback: () => void) => void;
  render: (
    element: HTMLElement,
    options: {
      sitekey: string;
      callback: (token: string) => void;
      'expired-callback': () => void;
      'error-callback': () => void;
    },
  ) => number;
  reset: (id: number) => void;
};
declare global {
  interface Window {
    grecaptcha?: Recaptcha;
    nuswhispersCaptchaReady?: () => void;
  }
}

let loading: Promise<Recaptcha> | undefined;
function loadCaptcha(): Promise<Recaptcha> {
  if (window.grecaptcha?.render) return Promise.resolve(window.grecaptcha);
  loading ??= new Promise((resolve, reject) => {
    window.nuswhispersCaptchaReady = () => {
      if (window.grecaptcha) resolve(window.grecaptcha);
    };
    const script = document.createElement('script');
    script.src =
      'https://www.google.com/recaptcha/api.js?onload=nuswhispersCaptchaReady&render=explicit';
    script.async = true;
    script.onerror = () => {
      loading = undefined;
      reject(new Error('Could not load reCAPTCHA. Please reload this page.'));
    };
    document.head.append(script);
  });
  return loading;
}

export function Captcha({
  onChange,
  resetKey,
}: {
  onChange: (token: string) => void;
  resetKey: number;
}) {
  const container = useRef<HTMLDivElement>(null);
  const widget = useRef<number | undefined>(undefined);
  const [error, setError] = useState('');
  const change = useEffectEvent(onChange);
  useEffect(() => {
    if (!config.recaptchaKey || !container.current) return;
    let active = true;
    const element = container.current;
    void loadCaptcha()
      .then((api) => {
        if (!active) return;
        widget.current = api.render(element, {
          sitekey: config.recaptchaKey,
          callback: (token) => {
            setError('');
            change(token);
          },
          'expired-callback': () => change(''),
          'error-callback': () => {
            change('');
            setError('reCAPTCHA failed. Please try again.');
          },
        });
      })
      .catch((error) => {
        if (active) setError(error.message);
      });
    return () => {
      active = false;
      element.replaceChildren();
      widget.current = undefined;
    };
  }, []);
  useEffect(() => {
    if (resetKey > 0 && widget.current !== undefined) window.grecaptcha?.reset(widget.current);
  }, [resetKey]);
  if (!config.recaptchaKey)
    return (
      <p className="text-red-700" role="alert">
        Submissions are unavailable because reCAPTCHA is not configured.
      </p>
    );
  return (
    <>
      <div ref={container} className="max-w-full overflow-x-auto" />
      {error && (
        <p role="alert" className="text-red-700">
          {error}
        </p>
      )}
    </>
  );
}
