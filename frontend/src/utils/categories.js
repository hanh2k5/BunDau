export const CATEGORY_MAP = {
  'bun-dau': 'Bún đậu',
  'bun-cha': 'Bún chả',
  'combo': 'Combo',
  'drink': 'Đồ uống',
  'other': 'Đồ thêm'
}

export const getCategoryLabel = (id) => {
  return CATEGORY_MAP[id] || 'Món chính'
}
