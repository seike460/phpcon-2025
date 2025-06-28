# Eloquent依存ロジック → Symfony移行デモ

PHPカンファレンス2025のスライド「なぜ適用するか、移行して理解するClean Architecture」のUse Case 2実装です。

## 概要

Eloquent依存が進んだロジックをSymfonyに移行する**段階的リファクタリングプロセス**を実証します。

## 移行段階

### Phase 1: Domain Entity抽出段階 (port 8084)
- EloquentモデルからPure PHP Entityを抽出
- ビジネスロジックをDomain層に分離
- **変更範囲**: Model層の大幅リファクタリング

### Phase 2: Repository Pattern導入段階 (port 8085)  
- データアクセス抽象化
- Repository Interface経由のデータ操作
- **変更範囲**: データアクセス層の完全書き直し

### Phase 3: UseCase層分離段階 (port 8086)
- Controller-Model密結合の解消
- アプリケーションロジック分離
- **変更範囲**: Controller層の大幅修正

### Phase 4: Symfony完全移行段階 (port 8087)
- EloquentからDoctrineへの完全移行
- Symfony DIコンテナ対応
- **変更範囲**: Infrastructure層の全面書き換え

## Clean Architectureとの比較

- **Use Case 1 (Clean Architecture)**: 移行時の変更はInfrastructure層のみ
- **Use Case 2 (Eloquent依存)**: 全層にわたる大規模リファクタリングが必要

## 実証する内容

1. **段階的移行の必要性**: フレームワーク依存度が高いと一度にSymfonyに移行できない
2. **リファクタリング工数**: Clean Architectureの10倍以上の工数が必要
3. **移行リスク**: 各段階でバグ混入の可能性
4. **設計継承の境界線**: フレームワーク依存度で移行難易度が決定される

## 動作環境

各段階を独立したWebアプリとして実装し、ブラウザで移行プロセスを可視化します。