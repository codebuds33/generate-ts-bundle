import {SubSubEntity1} from "./Sub1/SubSub1/SubSubEntity1"
import {SubSubEntity2} from "./Sub1/SubSub1/SubSubEntity2"
import {SubEntity1} from "./Sub1/SubEntity1"
import {SubEntity2} from "./Sub2/SubEntity2"

export interface Root {
  id: number;
  subSubEntity1: SubSubEntity1;
  subSubEntity2: Array<SubSubEntity2>;
  subEntity1: Array<SubEntity1>;
  subEntity2: SubEntity2;
  datetime: Date;
  text: string;
}
