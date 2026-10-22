import { Button } from '@base-ui/react/button';
import { Checkbox } from '@base-ui/react/checkbox';
import { Link } from '@void/react';
import { useState, type FormEvent } from 'react';
import { useFateClient, useViews } from 'react-fate';
import { Icon } from '../../components/icon';
import { primaryActionClasses } from '../../components/primary-action';
import { CategoryView, type AppSchema } from '../../lib/api';
import { config } from '../../lib/config';
import { useAppRequest } from '../../lib/use-app-request';
import { Captcha } from './captcha';
import {
  normalizeStory,
  recall,
  recordSubmission,
  remainingSubmissions,
  remember,
} from './storage';

export function Submit() {
  const client = useFateClient<AppSchema>();
  const { categories: categoryRefs } = useAppRequest({ categories: { list: CategoryView } });
  const categories = useViews(CategoryView, categoryRefs);
  const [content, setContent] = useState('');
  const [selected, setSelected] = useState<string[]>([]);
  const [captcha, setCaptcha] = useState('');
  const [resetKey, setResetKey] = useState(0);
  const [pending, setPending] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');
  const [remaining, setRemaining] = useState(remainingSubmissions);

  async function submit(event: FormEvent) {
    event.preventDefault();
    if (pending) return;
    const available = remainingSubmissions();
    setRemaining(available);
    if (available <= 0) return;
    const story = normalizeStory(content);
    if (!story || !captcha) {
      setError('Please enter your story and complete reCAPTCHA.');
      return;
    }
    setPending(true);
    setError('');
    try {
      const result = await client.mutations.submit({
        input: {
          content: story,
          categories: selected,
          captcha,
          fingerprint: recall(config.fingerprintStorageKey),
        },
      });
      if (result.error) throw new Error(result.error.message);
      const token = result.result?.[config.fingerprintApiKey];
      if (typeof token === 'string') remember(config.fingerprintStorageKey, token);
      recordSubmission();
      setRemaining(remainingSubmissions());
      setSuccess(true);
    } catch (error) {
      setError(
        error instanceof Error
          ? error.message
          : 'Could not submit your confession. Please try again.',
      );
    } finally {
      setPending(false);
      setCaptcha('');
      setResetKey((key) => key + 1);
    }
  }

  if (success)
    return (
      <output className="block bg-white p-6 sm:p-8">
        <div
          className="mb-5 grid size-10 place-items-center rounded-full bg-sky-50 text-2xl text-brand"
          aria-hidden="true"
        >
          <Icon name="tick" />
        </div>
        <h1 className="mb-4 text-2xl leading-tight font-normal">That's off your chest.</h1>
        <p className="text-sm leading-7 text-gray-500">
          Thanks for telling us your story! We'll read it as soon as we can.
        </p>
        <p className="mt-3 text-sm leading-7 text-gray-500">
          In the meantime, there's plenty of campus life to catch up on.
        </p>
        <Link className={`${primaryActionClasses} mt-6`} href="/">
          Read other confessions
        </Link>
        <div className="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs">
          <Button
            type="button"
            className="text-sm text-brand hover:underline"
            onClick={() => {
              setSuccess(false);
              setContent('');
              setSelected([]);
            }}
          >
            Write another one
          </Button>
          <span>
            or{' '}
            <a
              href="https://www.facebook.com/nuswhispers"
              target="_blank"
              rel="noopener noreferrer"
            >
              find us on Facebook
            </a>
          </span>
        </div>
      </output>
    );

  return (
    <>
      <h1 className="mb-3 px-1 text-2xl leading-tight font-normal sm:px-0">
        Got something on your mind?
      </h1>
      <div className="mb-6 space-y-3 px-1 text-sm leading-7 text-gray-500 sm:px-0">
        <p>
          Have a story to share, a question to ask, or just need to get something off your chest?
          You're in the right place.
        </p>
        <p>
          Keep it kind and socially responsible. No racial, religious or other sensitive material.
          These entries will be rejected (and make us sad).
        </p>
      </div>
      {remaining <= 0 && (
        <p className="mb-4 bg-red-50 p-5 text-sm text-red-800" role="alert">
          You've submitted too many confessions today! Please try again tomorrow.
        </p>
      )}
      <form className="space-y-7 bg-white p-5 sm:p-6" onSubmit={submit}>
        <div className="min-w-0">
          <label className="mb-3 block text-base font-medium" htmlFor="story">
            Your story
          </label>
          <textarea
            id="story"
            className="block min-h-52 w-full resize-y rounded-sm border-0 bg-gray-50 p-3 text-base leading-7 placeholder:text-gray-400 sm:p-4"
            placeholder="So, here's the thing…"
            value={content}
            onChange={(event) => setContent(event.target.value)}
            required
            rows={7}
          />
        </div>
        <fieldset className="min-w-0">
          <legend className="mb-3 block text-base font-medium">
            Pick a category{' '}
            <span className="mt-1 block text-xs font-normal text-gray-500 sm:mt-0 sm:ml-2 sm:inline">
              One or a few. It's up to you.
            </span>
          </legend>
          <div className="grid grid-cols-2 gap-x-2 gap-y-3 text-xs sm:grid-cols-3">
            {categories.map((category) => (
              <label key={category.id} className="flex min-h-9 cursor-pointer items-center gap-2">
                <Checkbox.Root
                  className="inline-flex size-4 shrink-0 items-center justify-center rounded-xs border border-gray-400 bg-white text-sm text-white data-[checked]:border-brand data-[checked]:bg-brand"
                  checked={selected.includes(String(category.id))}
                  onCheckedChange={(checked) =>
                    setSelected((ids) =>
                      checked
                        ? [...ids, String(category.id)]
                        : ids.filter((id) => id !== category.id),
                    )
                  }
                >
                  <Checkbox.Indicator>
                    <Icon name="tick" />
                  </Checkbox.Indicator>
                </Checkbox.Root>
                {category.name}
              </label>
            ))}
          </div>
        </fieldset>
        <div className="min-w-0">
          <h2 className="mb-3 block text-base font-medium">Humans only, please</h2>
          <Captcha onChange={setCaptcha} resetKey={resetKey} />
        </div>
        <div className="flex items-start gap-3 sm:items-center sm:gap-4">
          <Button
            className={primaryActionClasses}
            type="submit"
            disabled={pending || remaining <= 0 || !captcha}
          >
            {pending ? 'Submitting…' : 'Send it our way'}
          </Button>
          <span className="pt-0.5 text-xs leading-relaxed text-gray-500">
            Your submission is
            <br />
            totally anonymous.
          </span>
        </div>
        {error && (
          <p className="bg-red-50 p-5 text-sm text-red-800" role="alert">
            Oops! We could not submit your confession. {error}
          </p>
        )}
      </form>
      <p className="mt-5 px-1 text-xs leading-loose text-gray-500 sm:px-0">
        Submitting for an official organisation? Please{' '}
        <a href="mailto:admin@whispers.sg">email us</a> so we can verify it's you.
        <br />
        By submitting, you agree to our <Link href="/policy/">Privacy Policy</Link>.
      </p>
    </>
  );
}
