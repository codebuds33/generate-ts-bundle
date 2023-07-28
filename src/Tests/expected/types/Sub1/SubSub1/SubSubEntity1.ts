import {SubEntity2} from "../../Sub2/SubEntity2"
import {SubSubEntity2} from "./SubSubEntity2"
import {SubEntity1} from "../SubEntity1"

export type SubSubEntity1 = {
  id: number;
  subEntity2: SubEntity2;
  subSubEntity2: SubSubEntity2;
  subEntity1: SubEntity1;
}
