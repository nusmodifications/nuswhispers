import { Button } from '@base-ui/react/button';
import { Checkbox } from '@base-ui/react/checkbox';
import { Link } from '@void/react';
import { useState, type FormEvent } from 'react';
import { useFateClient, useViews } from 'react-fate';
import { CategoryView, type AppSchema } from './api';
import Captcha from './Captcha';
import { Icon } from './components';
import { config } from './config';
import {
  normalizeStory,
  recall,
  recordSubmission,
  remainingSubmissions,
  remember,
} from './storage';
import { useAppRequest } from './useAppRequest';

export default function Submit() {
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
      <output className="notice block">
        <p>
          Thanks for telling us your story! We'll read it as soon as we can. In the meantime, you
          can{' '}
          <Button
            className="text-brand underline"
            onClick={() => {
              setSuccess(false);
              setContent('');
              setSelected([]);
            }}
          >
            write another confession
          </Button>{' '}
          or <Link href="/home/">read other confessions</Link>.
        </p>
        <p className="mt-5">
          By the way, if you haven't already,{' '}
          <a href="https://www.facebook.com/nuswhispers" target="_blank" rel="noopener noreferrer">
            please like us on Facebook!
          </a>{' '}
          We'll love you long time. <Icon name="heart-full-outline" />
        </p>
      </output>
    );

  return (
    <form className="submit-form" onSubmit={submit}>
      <h1 className="fancy-header">Submit a Confession</h1>
      <div className="form-section space-y-3">
        <p>
          Have an interesting story to share or just need to get something off your chest? Tell us
          your story here! No one will know it was you.
        </p>
        <p>
          Please be reminded to be socially responsible. No racial, religious or other forms of
          sensitive material. These entries will be rejected (and make us sad).
        </p>
        <p>
          If you are an official organization / body that wish to submit a confession to
          NUSWhispers. Please verify your authenticity and submit via{' '}
          <a href="mailto:admin@whispers.sg">email</a>. Otherwise, they will be rejected.
        </p>
      </div>
      {remaining <= 0 && (
        <p className="notice error" role="alert">
          You've submitted too many confessions today! Please try again tomorrow.
        </p>
      )}
      <div className="form-section">
        <label className="form-heading" htmlFor="story">
          <Icon name="pencil" /> Your Story
        </label>
        <textarea
          id="story"
          className="story-input"
          placeholder="Tell us your story…"
          value={content}
          onChange={(event) => setContent(event.target.value)}
          required
          rows={6}
        />
      </div>
      <fieldset className="form-section">
        <legend className="sr-only">Category</legend>
        <div className="form-heading" aria-hidden="true">
          <Icon name="folder" /> Category
        </div>
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
          {categories.map((category) => (
            <label key={category.id} className="flex cursor-pointer items-center gap-2">
              <Checkbox.Root
                className="category-checkbox"
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
      <div className="form-section">
        <h2 className="form-heading">
          <Icon name="heart-full-outline" /> Don't Spam
        </h2>
        <Captcha onChange={setCaptcha} resetKey={resetKey} />
      </div>
      <div className="flex flex-wrap items-center gap-3">
        <Button className="button" type="submit" disabled={pending || remaining <= 0 || !captcha}>
          {pending ? 'Submitting…' : 'Submit'}
        </Button>
        <span className="text-[#8e8e8e]">Your submission is totally anonymous.</span>
      </div>
      {error && (
        <p className="notice error mt-3" role="alert">
          Oops! We could not submit your confession. {error}
        </p>
      )}
    </form>
  );
}
