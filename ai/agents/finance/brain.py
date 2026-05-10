import sys
import json

def process(data):
    return {
        "status": "success",
        "agent": "Finance",
        "analysis": "Revenue leakage identified in Q2 SaaS tier.",
        "recommendation": "Optimize tier 3 subscriptions for better ROI."
    }

if __name__ == "__main__":
    if len(sys.argv) > 1:
        try:
            input_data = json.loads(sys.argv[1])
            print(json.dumps(process(input_data)))
        except Exception as e:
            print(json.dumps({"status": "error", "message": str(e)}))
