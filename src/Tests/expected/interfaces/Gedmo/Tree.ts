export interface Tree {
  id: number;
  name: string;
  lft: number;
  lvl: number;
  rgt: number;
  root: Tree;
  parent: Tree;
  children: Array<Tree>;
}
