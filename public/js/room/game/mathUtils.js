/*
 * mathUtils.js
 * Simpler and calmer documentation for hex grid math utilities.
 */

// Radius of a hex in pixels
const radius = 25;
const SQRT3 = Math.sqrt(3);
const HEX_SIZE = radius;

// Axial neighbor offsets (E, NE, NW, W, SW, SE)
const AX_DIRECTIONS = [
  { dq: +1, dr:  0 },
  { dq: +1, dr: -1 },
  { dq:  0, dr: -1 },
  { dq: -1, dr:  0 },
  { dq: -1, dr: +1 },
  { dq:  0, dr: +1 },
];

/**
 * Convert axial coordinates (q, r) to pixel coordinates { x, y }.
 * Used to position hex shapes on the canvas.
 */
function axialToPixel(q, r) {
  return {
    x: HEX_SIZE * (3/2 * q),
    y: HEX_SIZE * (SQRT3/2 * q + SQRT3 * r),
  };
}

/**
 * Round fractional axial coordinates to the nearest valid axial coords.
 * This maintains the cube-coordinate constraint implicitly.
 */
function axialRound(q, r) {
  const s = -q - r;
  let rq = Math.round(q);
  let rr = Math.round(r);
  let rs = Math.round(s);

  const dq = Math.abs(rq - q);
  const dr = Math.abs(rr - r);
  const ds = Math.abs(rs - s);

  if (dq > dr && dq > ds) rq = -rr - rs;
  else if (dr > ds) rr = -rq - rs;

  return { q: rq, r: rr };
}

/**
 * Convert pixel coordinates (px, py) to axial coordinates (rounded).
 */
function pixelToAxial(px, py) {
  const q = (2/3 * px) / HEX_SIZE;
  const r = (-1/3 * px + SQRT3/3 * py) / HEX_SIZE;
  return axialRound(q, r);
}

/**
 * Return a simple string key for a hex at (q, r).
 */
function hexKey(q, r) {
  return `${q},${r}`;
}

/**
 * Return the 6 neighboring axial coordinates around (q, r).
 */
function axialNeighbors(q, r) {
  return AX_DIRECTIONS.map(d => ({ q: q + d.dq, r: r + d.dr }));
}

module.exports = {
  radius,
  HEX_SIZE,
  axialToPixel,
  axialRound,
  pixelToAxial,
  hexKey,
  axialNeighbors,
};
