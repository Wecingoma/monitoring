import numpy as np
from sklearn.ensemble import IsolationForest, RandomForestClassifier
from sklearn.preprocessing import StandardScaler
import joblib
import os
import logging

logger = logging.getLogger(__name__)


class AnomalyDetector:
    def __init__(self):
        self.models = {}
        self.scalers = {}
        self.model_dir = os.environ.get('MODEL_DIR', './models')
        self.detection_count = 0
        self.anomaly_count = 0
        os.makedirs(self.model_dir, exist_ok=True)
        self._load_models()
        if not self.models:
            self._initialize_default_models()

    def _load_models(self):
        metric_types = ['cpu', 'ram', 'network', 'disk']
        for mtype in metric_types:
            model_path = os.path.join(self.model_dir, f'isolation_forest_{mtype}.joblib')
            scaler_path = os.path.join(self.model_dir, f'scaler_{mtype}.joblib')
            if os.path.exists(model_path):
                self.models[mtype] = joblib.load(model_path)
                logger.info(f"Loaded model for {mtype}")
            if os.path.exists(scaler_path):
                self.scalers[mtype] = joblib.load(scaler_path)

    def _initialize_default_models(self):
        metric_types = ['cpu', 'ram', 'network', 'disk']
        for mtype in metric_types:
            n_estimators = 100
            contamination = 0.1
            model = IsolationForest(
                n_estimators=n_estimators,
                contamination=contamination,
                random_state=42,
                n_jobs=-1
            )
            scaler = StandardScaler()
            np.random.seed(42)
            normal_data = self._generate_normal_data(mtype, 1000)
            scaler.fit(normal_data)
            scaled_data = scaler.transform(normal_data)
            model.fit(scaled_data)
            self.models[mtype] = model
            self.scalers[mtype] = scaler
            self._save_model(mtype, model, scaler)
            logger.info(f"Initialized default model for {mtype}")

    def _generate_normal_data(self, metric_type, n_samples):
        np.random.seed(42)
        if metric_type == 'cpu':
            values = np.random.normal(35, 15, n_samples)
            values = np.clip(values, 5, 95)
        elif metric_type == 'ram':
            values = np.random.normal(55, 12, n_samples)
            values = np.clip(values, 20, 90)
        elif metric_type == 'network':
            values = np.random.normal(25, 10, n_samples)
            values = np.clip(values, 1, 80)
        elif metric_type == 'disk':
            values = np.random.normal(50, 15, n_samples)
            values = np.clip(values, 10, 95)
        else:
            values = np.random.normal(40, 15, n_samples)
            values = np.clip(values, 1, 99)
        timestamps = np.arange(n_samples, dtype=float)
        diffs = np.diff(values)
        diffs = np.append([0], diffs)
        features = np.column_stack([values, diffs, timestamps / n_samples])
        return features

    def _save_model(self, metric_type, model, scaler):
        model_path = os.path.join(self.model_dir, f'isolation_forest_{metric_type}.joblib')
        scaler_path = os.path.join(self.model_dir, f'scaler_{metric_type}.joblib')
        joblib.dump(model, model_path)
        joblib.dump(scaler, scaler_path)

    def is_loaded(self):
        return len(self.models) > 0

    def detect(self, metrics, metric_type='cpu', server_name='unknown'):
        self.detection_count += 1

        if metric_type not in self.models:
            self._initialize_default_models()

        model = self.models.get(metric_type)
        scaler = self.scalers.get(metric_type)

        if model is None:
            return {
                'anomaly': False,
                'score': 0.0,
                'severity': 'low',
                'description': f'Modèle non disponible pour {metric_type}',
                'recommendation': 'Entraîner le modèle avec plus de données',
                'model_info': {'model': 'none', 'version': '0.0'}
            }

        values = [m.get('value', 0) for m in metrics]
        if len(values) < 2:
            return {
                'anomaly': False,
                'score': 0.0,
                'severity': 'low',
                'description': 'Données insuffisantes pour l\'analyse',
                'recommendation': 'Collecter plus de données',
                'model_info': {'model': 'IsolationForest', 'version': '1.0'}
            }

        values_array = np.array(values, dtype=float)
        diffs = np.diff(values_array)
        diffs = np.append([0], diffs)
        features = np.column_stack([
            values_array,
            diffs,
            np.linspace(0, 1, len(values))
        ])

        if scaler:
            features_scaled = scaler.transform(features)
        else:
            features_scaled = features

        predictions = model.predict(features_scaled)
        scores = model.decision_function(features_scaled)

        avg_score = float(np.mean(np.abs(scores)))
        anomaly_ratio = float(np.sum(predictions == -1)) / len(predictions)
        current_value = values[-1]
        current_diff = diffs[-1] if len(diffs) > 0 else 0
        is_anomaly = anomaly_ratio > 0.3 or avg_score > 0.5
        severity = self._determine_severity(avg_score, current_value, metric_type)
        description = self._generate_description(metric_type, current_value, avg_score, is_anomaly)
        recommendation = self._generate_recommendation(metric_type, severity, current_value)

        if is_anomaly:
            self.anomaly_count += 1

        result = {
            'anomaly': is_anomaly,
            'score': round(avg_score * 100) / 100,
            'severity': severity,
            'description': description,
            'recommendation': recommendation,
            'data_points': {
                'current_value': round(current_value, 2),
                'average_value': round(float(np.mean(values)), 2),
                'max_value': round(float(np.max(values)), 2),
                'min_value': round(float(np.min(values)), 2),
                'trend': 'increasing' if current_diff > 0 else 'decreasing',
                'anomaly_ratio': round(anomaly_ratio * 100, 2),
            },
            'model_info': {
                'model': 'IsolationForest',
                'version': '1.0',
                'contamination': model.contamination if hasattr(model, 'contamination') else 0.1,
                'n_estimators': model.n_estimators if hasattr(model, 'n_estimators') else 100,
            }
        }

        return result

    def _determine_severity(self, score, current_value, metric_type):
        thresholds = {
            'cpu': {'critical': 90, 'warning': 70},
            'ram': {'critical': 90, 'warning': 75},
            'network': {'critical': 70, 'warning': 50},
            'disk': {'critical': 90, 'warning': 80},
        }
        t = thresholds.get(metric_type, {'critical': 85, 'warning': 70})

        if score > 0.7 or current_value > t['critical']:
            return 'critical'
        elif score > 0.4 or current_value > t['warning']:
            return 'warning'
        return 'low'

    def _generate_description(self, metric_type, value, score, is_anomaly):
        labels = {
            'cpu': 'CPU', 'ram': 'mémoire RAM', 'network': 'réseau',
            'disk': 'disque', 'behavior': 'comportement', 'security': 'sécurité'
        }
        label = labels.get(metric_type, metric_type)

        if is_anomaly:
            if score > 0.7:
                return f"Anomalie critique détectée: utilisation {label} à {value:.1f}%"
            elif score > 0.4:
                return f"Anomalie suspectée: utilisation {label} à {value:.1f}%"
            return f"Légère anomalie {label} détectée ({value:.1f}%)"
        return f"Utilisation {label} normale ({value:.1f}%)"

    def _generate_recommendation(self, metric_type, severity, value):
        recommendations = {
            'cpu': {
                'critical': 'Vérifier immédiatement les processus consommateurs, risque de saturation',
                'warning': 'Surveiller les processus actifs, envisager une optimisation',
                'low': 'Aucune action nécessaire, charge CPU normale'
            },
            'ram': {
                'critical': 'Risque de saturation mémoire, envisager une augmentation de RAM ou optimiser les processus',
                'warning': 'Consommation mémoire élevée, surveiller les fuites mémoire potentielles',
                'low': 'Aucune action nécessaire, consommation mémoire normale'
            },
            'network': {
                'critical': 'Trafic réseau anormal détecté, possible attaque DDoS ou compromission',
                'warning': 'Trafic réseau inhabituel, analyser les connexions réseau',
                'low': 'Aucune action nécessaire, trafic réseau normal'
            },
            'disk': {
                'critical': 'Espace disque critique, libérer de l\'espace immédiatement',
                'warning': 'Espace disque diminuant, planifier un nettoyage ou une extension',
                'low': 'Aucune action nécessaire, espace disque suffisant'
            }
        }
        return recommendations.get(metric_type, recommendations.get('cpu', {})).get(severity, 'Aucune recommandation')

    def detect_all_patterns(self):
        results = []
        metric_types = ['cpu', 'ram', 'network', 'disk']
        for mtype in metric_types:
            np.random.seed(int(np.random.randint(0, 10000)))
            synthetic_data = self._generate_normal_data(mtype, 50)
            model = self.models.get(mtype)
            scaler = self.scalers.get(mtype)
            if model and scaler:
                scaled = scaler.transform(synthetic_data)
                scores = model.decision_function(scaled)
                predictions = model.predict(scaled)
                anomaly_count = int(np.sum(predictions == -1))
                if anomaly_count > 0:
                    max_score = float(np.max(np.abs(scores)))
                    severity = self._determine_severity(max_score, float(np.mean(synthetic_data[:, 0])), mtype)
                    results.append({
                        'metric_type': mtype,
                        'anomaly_count': anomaly_count,
                        'max_score': round(max_score, 2),
                        'severity': severity,
                    })
        return results

    def get_stats(self):
        return {
            'total_detections': self.detection_count,
            'total_anomalies': self.anomaly_count,
            'anomaly_rate': round(self.anomaly_count / max(self.detection_count, 1) * 100, 2),
            'model_types': list(self.models.keys()),
            'models_loaded': len(self.models),
        }

    def train(self, metric_type, data):
        values = [d.get('value', 0) for d in data]
        if len(values) < 10:
            return {'error': 'Need at least 10 data points'}

        values_array = np.array(values, dtype=float)
        diffs = np.diff(values_array)
        diffs = np.append([0], diffs)
        features = np.column_stack([
            values_array,
            diffs,
            np.linspace(0, 1, len(values))
        ])

        scaler = StandardScaler()
        scaler.fit(features)
        scaled_features = scaler.transform(features)

        model = IsolationForest(
            n_estimators=100,
            contamination=0.1,
            random_state=42,
            n_jobs=-1
        )
        model.fit(scaled_features)

        self.models[metric_type] = model
        self.scalers[metric_type] = scaler
        self._save_model(metric_type, model, scaler)

        return {
            'status': 'trained',
            'metric_type': metric_type,
            'data_points': len(values),
            'model': 'IsolationForest',
        }