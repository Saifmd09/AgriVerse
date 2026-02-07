def predict_with_ml(task, features):
    if task == "yield":
        rainfall = features.get("rainfall_mm", 0)
        fertilizer = features.get("fertilizer_kg_per_ha", 0)
        prediction = (rainfall * 0.01) + (fertilizer * 0.02)
        return {"prediction": round(prediction, 2)}

    if task == "pest_risk":
        humidity = features.get("humidity", 50)
        return {"prediction": round(min(1, humidity / 100), 2)}

    if task == "fertilizer_need":
        deficit = features.get("nitrogen_deficit_kg", 30)
        return {"prediction": deficit}

    if task == "rainfall_risk":
        recent = features.get("recent_7days_mm", 40)
        return {"prediction": round(min(1, recent / 100), 2)}

    return {"prediction": "Demo model"}
