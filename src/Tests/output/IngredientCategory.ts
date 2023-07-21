export interface IngredientCategory {
  id: number;
  name: string;
  lft: number;
  lvl: number;
  rgt: number;
  root: IngredientCategory;
  parent: IngredientCategory;
  children: Array<IngredientCategory>;
}
