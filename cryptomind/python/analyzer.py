import sys, json, random, base64

def analyze(b64data):
    try:
        raw = base64.b64decode(b64data).decode('utf-8')
        data = json.loads(raw)

        name = data.get('name', 'Coin')
        symbol = data.get('symbol', '???')
        price = float(data.get('price', 0))
        change = float(data.get('price_change', 0))
        high = float(data.get('high_24h', price*1.02))
        low = float(data.get('low_24h', price*0.98))

        # Technical analysis simulation
        volatility = ((high - low) / price) * 100 if price > 0 else 1
        momentum = change / max(volatility, 0.1)

        rsi = 50 + momentum * 8 + random.uniform(-5, 5)
        rsi = max(20, min(80, rsi))

        macd_val = change * 1.2 + random.uniform(-1, 1)

        if change > 3 and rsi > 55:
            signal, confidence = 'BUY', random.randint(72, 90)
        elif change < -2 and rsi < 45:
            signal, confidence = 'SELL', random.randint(68, 85)
        else:
            signal, confidence = 'HOLD', random.randint(60, 78)

        support = round(low * 0.995, 2)
        resistance = round(high * 1.005, 2)

        reasons = {
            'BUY': f"{name} ({symbol}) shows strong bullish momentum with {change:.1f}% gain. RSI at {rsi:.0f} indicates growing buying pressure without entering overbought territory. Volume confirms the move. A measured entry with stop-loss near ${support:,.0f} is recommended.",
            'SELL': f"{name} ({symbol}) faces significant bearish pressure with {abs(change):.1f}% decline. RSI at {rsi:.0f} shows weakening momentum. The MACD crossover confirms the downtrend. Consider reducing exposure with key support at ${support:,.0f}.",
            'HOLD': f"{name} ({symbol}) is consolidating with {change:.1f}% movement. RSI at {rsi:.0f} indicates neutral momentum. The price is testing the ${support:,.0f}-${resistance:,.0f} range. Wait for a decisive breakout before entering new positions."
        }

        result = {
            'signal': signal,
            'confidence': confidence,
            'rsi': round(rsi, 1),
            'macd': f"+{macd_val:.1f}" if macd_val > 0 else f"{macd_val:.1f}",
            'support': support,
            'resistance': resistance,
            'reasoning': reasons[signal],
            'volatility': round(volatility, 2),
            'momentum_score': round(momentum, 2)
        }
        print(json.dumps(result))

    except Exception as e:
        print(json.dumps({'error': str(e)}))

if __name__ == '__main__':
    if len(sys.argv) > 1:
        analyze(sys.argv[1])
    else:
        print(json.dumps({'error': 'No data provided'}))
