import {Tomato} from "../Ingredients/Tomato"
import {Cucumber} from "../Ingredients/Cucumber"

export interface Salade {
  id: number;
  tomato: Array<Tomato>;
  cucumber: Cucumber;
}
