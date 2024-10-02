import type { MeetingPartialUpdate } from 'bmlt-root-server-client';
import { spinner } from '../stores/spinner';

export type GeocodeResult = {
  lat: number;
  lng: number;
  county: string;
  zipCode: string;
};

type GeocodeResponse = {
  results: google.maps.GeocoderResult[] | null;
  status: google.maps.GeocoderStatus;
};

const POSTAL_CODE_TYPE = 'postal_code';
const ADMIN_AREA_LEVEL_2 = 'administrative_area_level_2';
const COUNTY_SUFFIX = ' County';

function removeCountySuffix(county: string): string {
  return county.endsWith(COUNTY_SUFFIX) ? county.slice(0, -COUNTY_SUFFIX.length) : county;
}

function logError(message: string, error?: unknown): void {
  console.error(message, error);
}

function promisifyGeocode(geocoder: google.maps.Geocoder, address: string): Promise<GeocodeResponse> {
  return new Promise<GeocodeResponse>((resolve) => {
    geocoder.geocode({ address }, (results, status) => {
      resolve({ results, status });
    });
  });
}

export class Geocoder {
  private readonly address: string;

  constructor(meeting: MeetingPartialUpdate) {
    if (!meeting.locationNation) {
      meeting.locationNation = settings.regionBias;
    }

    this.address = [
      meeting.locationStreet,
      meeting.locationCitySubsection,
      meeting.locationMunicipality,
      meeting.locationProvince,
      meeting.locationSubProvince,
      meeting.locationPostalCode1,
      meeting.locationNation
    ]
      .filter(Boolean)
      .join(', ');
  }

  private async geocodeWithGoogle(): Promise<GeocodeResult | string> {
    const geocoder = new google.maps.Geocoder();
    const { results, status } = await promisifyGeocode(geocoder, this.address);

    if (status === google.maps.GeocoderStatus.OK && results) {
      const location = results[0].geometry.location;
      let county = '';
      let zipCode = '';

      results[0].address_components.forEach((component) => {
        if (component.types.includes(POSTAL_CODE_TYPE)) {
          zipCode = component.long_name;
        }
        if (component.types.includes(ADMIN_AREA_LEVEL_2)) {
          county = removeCountySuffix(component.long_name);
        }
      });

      return {
        lat: location.lat(),
        lng: location.lng(),
        county,
        zipCode
      };
    } else {
      logError('Google Geocoding failed:', status);
      return `Google Geocoding failed: ${status}`;
    }
  }

  private async geocodeWithNominatim(): Promise<GeocodeResult | string> {
    const nominatimUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.address)}`;
    const response = await fetch(nominatimUrl);
    const data = await response.json();

    if (data && data.length > 0) {
      const result = data[0];
      const lat = parseFloat(result.lat);
      const lon = parseFloat(result.lon);
      let county = '';
      let zipCode = '';

      if (settings.countyAutoGeocodingEnabled || settings.zipAutoGeocodingEnabled) {
        const reverseUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`;
        const reverseResponse = await fetch(reverseUrl);
        const reverseData = await reverseResponse.json();
        const address = reverseData.address || {};
        county = removeCountySuffix(address.county || '');
        zipCode = address.postcode || '';
      }

      return { lat, lng: lon, county, zipCode };
    } else {
      logError('Nominatim Geocoding failed: No results found');
      return 'Nominatim Geocoding failed: No results found';
    }
  }

  public async geocode(): Promise<GeocodeResult | string> {
    spinner.show();
    const result = settings.googleApiKey ? await this.geocodeWithGoogle() : await this.geocodeWithNominatim();
    spinner.hide();
    return result;
  }
}
