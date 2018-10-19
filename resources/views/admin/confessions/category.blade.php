<div class="form-check mb-1">
    <input id="category-{{ $category->getKey() }}" name="categories[]" class="form-check-input" type="checkbox" value="{{ $category->getKey() }}"
        {{ $confession->categories->contains($category->getKey()) ? 'checked' : ''}}>
    <label for="category-{{ $category->getKey() }}" class="form-check-label">{{ $category->confession_category }}</label>
</div>
