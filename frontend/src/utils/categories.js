export const CATEGORY_MAP = {
  'bun-dau': 'Bún đậu',
  'bun-cha': 'Bún chả',
  'combo': 'Combo',
  'drink': 'Đồ uống',
  'other': 'Đồ thêm'
}

export const categories = [
  { id: 'bun-dau', label: 'Bún đậu', icon: '🍱' },
  { id: 'bun-cha', label: 'Bún chả', icon: '🍜' },
  { id: 'combo', label: 'Combo', icon: '🔥' },
  { id: 'drink', label: 'Đồ uống', icon: '🥤' },
  { id: 'other', label: 'Món thêm', icon: '🍥' }
]

export const getCategoryLabel = (id) => {
  return CATEGORY_MAP[id] || 'Món chính'
}
