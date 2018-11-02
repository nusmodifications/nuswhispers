import { fetchTopTags } from '../tags';

xdescribe('fetchTopTags', () => {
  it('works', async () => {
    const tags = await fetchTopTags();
    expect(tags.entities).toBeDefined();
  });
});
