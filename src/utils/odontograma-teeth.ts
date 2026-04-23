export interface Tooth {
  id: number;
}

export const adultTeeth: Tooth[] = [
  { id: 18 }, { id: 17 }, { id: 16 }, { id: 15 }, { id: 14 }, { id: 13 }, { id: 12 }, { id: 11 },
  { id: 21 }, { id: 22 }, { id: 23 }, { id: 24 }, { id: 25 }, { id: 26 }, { id: 27 }, { id: 28 },
  { id: 48 }, { id: 47 }, { id: 46 }, { id: 45 }, { id: 44 }, { id: 43 }, { id: 42 }, { id: 41 },
  { id: 31 }, { id: 32 }, { id: 33 }, { id: 34 }, { id: 35 }, { id: 36 }, { id: 37 }, { id: 38 },
];

export const childTeeth: Tooth[] = [
  { id: 55 }, { id: 54 }, { id: 53 }, { id: 52 }, { id: 51 },
  { id: 61 }, { id: 62 }, { id: 63 }, { id: 64 }, { id: 65 },
  { id: 85 }, { id: 84 }, { id: 83 }, { id: 82 }, { id: 81 },
  { id: 71 }, { id: 72 }, { id: 73 }, { id: 74 }, { id: 75 },
];

export interface TeethByAgeResult {
  showAdult: boolean;
  showChild: boolean;
  adultTeeth: Tooth[];
  childTeeth: Tooth[];
}

export function getTeethByAge(age?: number): TeethByAgeResult {
  const isValidAge = typeof age === 'number' && Number.isFinite(age);

  if (!isValidAge) {
    return {
      showAdult: true,
      showChild: true,
      adultTeeth,
      childTeeth,
    };
  }

  if (age < 6) {
    return {
      showAdult: false,
      showChild: true,
      adultTeeth: [],
      childTeeth,
    };
  }

  if (age <= 11) {
    return {
      showAdult: true,
      showChild: true,
      adultTeeth,
      childTeeth,
    };
  }

  return {
    showAdult: true,
    showChild: false,
    adultTeeth,
    childTeeth: [],
  };
}
