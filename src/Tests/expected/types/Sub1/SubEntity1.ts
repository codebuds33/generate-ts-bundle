import {Root} from "../Root"
import {SubSubEntity1} from "./SubSub1/SubSubEntity1"

export type SubEntity1 = {
  id: number;
  roots: Array<Root>;
  subSubEntity1: SubSubEntity1;
}
