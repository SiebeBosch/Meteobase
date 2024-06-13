# test_stats_app.py


import logging
import unittest
from unittest.mock import MagicMock, patch
import numpy as np

# Mocking the wiwb module and its functions
mock_wiwb = MagicMock()
mock_wiwb.GEVCDF.return_value = np.random.rand(5)
mock_wiwb.GEVINVERSE.return_value = np.random.rand(5)
mock_wiwb.GLOCDF.return_value = np.random.rand(5)
mock_wiwb.GLOINVERSE.return_value = np.random.rand(5)
mock_wiwb.AREA.return_value = [{'T_series_Langbein': 1, 'val1': 1, 'val2': 2}]
mock_wiwb.xy_series.return_value.toGDT.return_value = 'GDT_MOCK'

with patch.dict('sys.modules', {'wiwb.stats_lib': mock_wiwb, 'wiwb.series_lib': mock_wiwb}):
    from stats_app import (
        get_2015_GEV_params, get_2019_params, vols_2019_huidig, rp_2019_huidig,
        factors_2019, calculate_verandergetal, verandergetalfunctie_jaarrond,
        verandergetalfunctie_winter, get_verander_getal, vols_2024, rp_2024,
        vols_2019, rp_2019
    )

    class TestStatsAppFunctions(unittest.TestCase):

        def setUp(self):
            self.durations = [1/6, 1/2, 1, 2, 4, 8, 12, 24, 48, 96, 192, 240]
            self.rp = [0.5, 1, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000]
            self.volumes = [10, 20, 30, 40, 50, 75, 100, 150]
            self.climate = '2024'
            self.season = 'jaarrond'
            self.scenario = '-'

        def test_get_2015_GEV_params(self):
            climate = '2014'
            scenario = '-'
            season = 'summer'
            region = '-'
            durations = np.array([24, 48, 96])
            mu, sigma, Zeta = get_2015_GEV_params(climate, scenario, season, region, durations)
            self.assertEqual(len(mu), len(durations))
            self.assertEqual(len(sigma), len(durations))
            self.assertEqual(len(Zeta), len(durations))

        def test_get_2019_params(self):
            durations = self.durations
            season = self.season
            mu, sigma, Zeta = get_2019_params(durations, season)
            self.assertEqual(len(mu), len(durations))
            self.assertEqual(len(sigma), len(durations))
            self.assertEqual(len(Zeta), len(durations))

        def test_vols_2019_huidig(self):
            pars_default = get_2019_params(self.durations, self.season)
            pars_high = get_2019_params(self.durations, self.season, return_period=121)
            rp = np.array(self.rp)
            prob = np.exp(-1 / rp)
            vols = vols_2019_huidig(pars_default, pars_high, rp, prob, self.durations, self.season)
            self.assertEqual(vols.shape[0], len(rp))
            self.assertEqual(vols.shape[1], len(self.durations))

        def test_rp_2019_huidig(self):
            pars_default = get_2019_params(self.durations, self.season)
            pars_high = get_2019_params(self.durations, self.season, return_period=121)
            vols = np.random.rand(len(self.rp), len(self.durations))
            rp = rp_2019_huidig(pars_default, pars_high, vols, self.durations, self.season)
            self.assertEqual(rp.shape[0], len(vols))
            self.assertEqual(rp.shape[1], len(self.durations))

        def test_factors_2019(self):
            prob = np.exp(-1 / np.array(self.rp))
            durations = np.array(self.durations)

            # Log inputs for debugging
            logging.info(f"Testing factors_2019 with durations: {durations}, prob: {prob}, season: {self.season}")

            try:
                factors = factors_2019(durations, prob, self.season, '2014', '-')
            except IndexError as e:
                logging.error(f"Test failed with IndexError: {e}")
                self.fail(f"factors_2019 raised IndexError: {e}")

            # Check if 'factors['midden']' matches the expected shape
            self.assertEqual(factors['midden'].shape, (len(durations),))

        def test_calculate_verandergetal(self):
            D = 50
            Ts = 2
            v_values = [1.234, 1.5, 1.1]
            result = calculate_verandergetal(D, Ts, v_values)
            self.assertIsNotNone(result)

        def test_vols_2024(self):
            vols = vols_2024(self.rp, self.durations, self.season, self.climate, self.scenario)
            self.assertEqual(vols.shape[0], len(self.rp))
            self.assertEqual(vols.shape[1], len(self.durations))

        def test_rp_2024(self):
            vols = np.random.rand(len(self.rp), len(self.durations))
            rp = rp_2024(vols, self.durations, self.season, self.climate, self.scenario)
            self.assertEqual(rp.shape[0], len(vols))
            self.assertEqual(rp.shape[1], len(self.durations))

        def test_vols_2019(self):
            vols = vols_2019(self.rp, self.durations, self.season, self.climate, self.scenario)
            self.assertEqual(vols.shape[0], len(self.rp))
            self.assertEqual(vols.shape[1], len(self.durations))

        def test_rp_2019(self):
            vols = np.random.rand(len(self.volumes), len(self.durations))
            rp = rp_2019(vols, self.durations, self.season, self.climate, self.scenario)
            self.assertEqual(rp.shape[0], len(vols))
            self.assertEqual(rp.shape[1], len(self.durations))

    if __name__ == '__main__':
        unittest.main()
