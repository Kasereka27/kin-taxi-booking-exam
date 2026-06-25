/** Tarifs Kinshasa — prise en charge + FC/km (alignés sur app/Models/Ride.php) */
export const RIDE_RATES = {
  eco: { base: 2000, km: 800 },
  confort: { base: 3500, km: 1200 },
  van: { base: 5000, km: 1800 },
};

/**
 * @param {string} vehicleType
 * @param {number} distanceKm
 */
export function estimateRidePrice(vehicleType, distanceKm) {
  const rate = RIDE_RATES[vehicleType] ?? RIDE_RATES.eco;

  return Math.round(rate.base + distanceKm * rate.km);
}
