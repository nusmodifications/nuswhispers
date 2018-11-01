import { fetchCategories } from '../categories';

xdescribe('fetchCategories', () => {
  it('works', async () => {
    const categories = await fetchCategories();
    expect(categories.entities).toBeDefined();
  });
});
